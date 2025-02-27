<?php

namespace App\Services;

use App\Models\MCustomerStatus;
use App\Models\MPaymentTermStatus;
use App\Models\MRole;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MasterDataService extends Service
{
    public const DRIVER_ELOQUENT = 'eloquent';
    public const DEFAULT_PER_PAGE = 20;

    /**
     * @var array
     */
    protected array $resources = [];

    /**
     * @var array
     */
    protected $availableResources = [
        'roles' => [
            'driver' => self::DRIVER_ELOQUENT,
            'target' => MRole::class,
            'select' => ['id', 'name'],
        ],
        'payment_term_statuses' => [
            'driver' => self::DRIVER_ELOQUENT,
            'target' => MPaymentTermStatus::class,
            'select' => ['id', 'name'],
        ],
        'customer_statuses' => [
            'driver' => self::DRIVER_ELOQUENT,
            'target' => MCustomerStatus::class,
            'select' => ['id', 'name'],
        ],
    ];

    /**
     * @var null
     */
    protected $data = null;

    /**
     * With resources
     *
     * @param  array  $resources
     * @return $this
     */
    public function withResources(array $resources)
    {
        $rs = [];
        foreach ($resources as $resourceName => $resourceParams) {
            if ($this->isAvailableResource($resourceName)) {
                $rs[] = [
                    'name' => $resourceName,
                    'params' => $this->decodeParams($resourceParams),
                ];
            }//end if
        }//end foreach

        $this->resources = $rs;

        return $this;
    }

    /**
     * Check if is available resource
     *
     * @param $resourceName
     * @return bool
     */
    protected function isAvailableResource($resourceName): bool
    {
        if (!isset($this->availableResources[$resourceName])) {
            return false;
        }//end if

        return true;
    }

    /**
     * Decode input params
     *
     * @param  string  $params
     * @return array
     */
    protected function decodeParams(string $params): array
    {
        try {
            if (empty($params)) {
                return [];
            }//end if

            return json_decode($params, true);
        } catch (Exception $e) {
            return [];
        }//end try
    }

    /**
     * Handle load resource from config driver
     *
     * @param  array  $resource
     * @param  array  $resourceConfig
     * @return array
     */
    protected function handleLoadFromConfig(array $resource, array $resourceConfig): array
    {
        return config($resourceConfig['target']);
    }

    /**
     * Handle load resource from eloquent driver
     *
     * @param  array  $resource
     * @param  array  $resourceConfig
     * @return array|Collection
     */
    protected function handleLoadFromEloquent(array $resource, array $resourceConfig): array|Collection
    {
        $query = $resourceConfig['target']::query();
        if (!empty($resourceConfig['select'])) {
            $query->select($resourceConfig['select']);
        }//end if

        if (!empty($resourceConfig['order'])) {
            $query->orderBy($resourceConfig['order'][0], $resourceConfig['order'][1]);
        }//end if

        return $query->get();
    }

    /**
     * Handle load resource from custom driver
     *
     * @param  array  $resource
     * @param  array  $resourceConfig
     * @return array|Collection
     */
    protected function handleLoadFromCustom(array $resource, array $resourceConfig): array|Collection
    {
        return $this->{$resourceConfig['target']}($resource, $resourceConfig);
    }

    /**
     * Handle load resource data
     *
     * @param  array  $resource
     * @return array|Collection
     */
    protected function handleLoad(array $resource): array|Collection
    {
        $resourceConfig = $this->availableResources[$resource['name']];
        $data = $this->{'handleLoadFrom' . Str::studly($resourceConfig['driver'])}($resource, $resourceConfig);
        if (empty($resourceConfig['convert_array'])) {
            return $data;
        }//end if

        return $data instanceof Collection ? $data->values() : collect($data)->values();
    }

    /**
     * Load data
     *
     * @return array
     */
    public function load(): array
    {
        $data = [];
        foreach ($this->resources as $resource) {
            if ($this->canGetResource($resource)) {
                $data[$resource['name']] = $this->handleLoad($resource);
            } else {
                $data[$resource['name']] = null;
            }//end if
        }//end foreach

        $this->data = $data;

        return $data;
    }

    /**
     * Get data
     *
     * @return array|null
     */
    public function get(): ?array
    {
        if (!$this->data) {
            $this->load();
        }//end if

        return $this->data;
    }

    /**
     * Check user login can get resource
     *
     * @param  array  $resource
     * @return bool
     */
    protected function canGetResource(array $resource): bool
    {
        $resourceConfig = $this->availableResources[$resource['name']];

        if (empty($resourceConfig['auth'])) {
            return true;
        }//end if

        if (!$this->user) {
            return false;
        }//end if

        foreach ($resourceConfig['auth'] as $authName) {
            if ($this->user->{'is' . Str::studly($authName)}()) {
                return true;
            }//end if
        }//end foreach

        return false;
    }

    /**
     * Get paginate params from resource params
     *
     * @param  array  $params
     * @return array
     */
    protected function getPaginateParams(array $params): array
    {
        $page = empty($params['page']) ? 1 : intval($params['page']);
        $perPage = empty($params['per_page']) ? self::DEFAULT_PER_PAGE : intval($params['per_page']);
        $search = empty($params['search']) ? '' : trim($params['search']);

        if ($page <= 0) {
            $page = 1;
        }//end if

        if ($perPage <= 0) {
            $perPage = self::DEFAULT_PER_PAGE;
        }//end if

        return [
            'per_page' => $perPage,
            'current_page' => $page,
            'search' => $search,
        ];
    }

    /**
     * @param $params
     * @return array
     */
    protected function getSelectedItems($params): array
    {
        if (empty($params['selected']) || !is_array($params['selected'])) {
            return [];
        }//end if

        $selectedIds = [];
        foreach ($params['selected'] as $selectedId) {
            $selectedIds[] = intval($selectedId);
        }//end foreach

        return $selectedIds;
    }

    /**
     * Get resource with paginate
     *
     * @param $resource
     * @param $query
     * @param  null  $searchField
     * @return array
     */
    protected function paginate($resource, $query, $searchField = null): array
    {
        $params = $resource['params'];
        $pageParams = $this->getPaginateParams($params);
        if ($searchField && $pageParams['search']) {
            $query->where($searchField, 'like', '%' . $pageParams['search'] . '%');
        }//end if

        $selectedIds = $this->getSelectedItems($params);
        $selectedCount = count($selectedIds);
        if ($selectedCount) {
            return $this->paginateWithSelected($query, $pageParams, $selectedIds);
        }//end if

        return $this->paginateNoSelected($query, $pageParams);
    }

    /**
     * @param $query
     * @param $pageParams
     * @param $selectedIds
     * @return array
     */
    protected function paginateWithSelected($query, $pageParams, $selectedIds): array
    {
        $fromTable = $query->getQuery()->from;
        $limit = $pageParams['per_page'];
        $selectedItems = collect([]);
        $data = collect([]);
        if ($pageParams['current_page'] == 1) {
            $selectedItemQuery = clone $query;
            $selectedItems = $selectedItemQuery->whereIn($fromTable . '.id', $selectedIds)->get();
            $limit = $limit - $selectedItems->count();
        }//end if

        $query->whereNotIn($fromTable . '.id', $selectedIds);

        $total = $query->count();
        $offset = $pageParams['per_page'] * ($pageParams['current_page'] - 1);
        $totalPage = ceil($total / $pageParams['per_page']);
        if ($limit > 0) {
            $data = $query->offset($offset)->limit($limit)->get();
        }//end if

        return [
            'data' => $selectedItems->merge($data)->toArray(),
            'per_page' => $pageParams['per_page'],
            'total_page' => (!$totalPage && $total > 0) ? 1 : $totalPage,
            'current_page' => $pageParams['current_page'],
            'total' => $total + $selectedItems->count(),
        ];
    }

    /**
     * @param $query
     * @param $pageParams
     * @return array
     */
    protected function paginateNoSelected($query, $pageParams): array
    {
        $total = $query->count();
        $offset = $pageParams['per_page'] * ($pageParams['current_page'] - 1);
        $data = $query->offset($offset)->limit($pageParams['per_page'])->get();

        return [
            'data' => $data->toArray(),
            'per_page' => $pageParams['per_page'],
            'total_page' => ceil($total / $pageParams['per_page']),
            'current_page' => $pageParams['current_page'],
            'total' => $total,
        ];
    }
}
