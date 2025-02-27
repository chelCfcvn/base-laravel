<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class BaseResource extends JsonResource
{
    protected array $exceptAttributes = [];
    protected array $attributes;
    protected array $dateAttributes = [
        'updated_at',
    ];
    protected array $monthAttributes = [
    ];
    protected array|null $onlyAttributes = null;

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
        $this->attributes ??= $this->initAttributes();
        $this->sanitizeAttributes();
    }

    protected function sanitizeAttributes(): void
    {
        if (in_array('*', $this->attributes, true)) {
            $index = array_search('*', $this->attributes, true);
            unset($this->attributes[$index]);
            $fillable = $this->resource->getFillable();
            array_unshift($fillable, $this->resource->getKeyName());

            if ($this->resource->timestamps) {
                $fillable = array_merge($fillable, [$this->resource->getCreatedAtColumn(), $this->resource->getUpdatedAtColumn()]);
            }

            $fillable = array_diff($fillable, $this->exceptAttributes);
            $this->attributes = array_merge($fillable, $this->attributes);
        }
    }

    /**
     * Because you can't set a callback as value when you define attributes
     * Example:
     * protected array $attributes = [
     *      'key' => fn ($resource) => $resource->value // This will not work
     * ];
     *
     * But it will work when you define attributes in a function
     *
     * @return array
     */
    protected function initAttributes(): array
    {
        // Example:
        // return [
        //     'key' => fn ($resource) => $resource->value
        // ];
        return [];
    }

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $data = [];

        foreach ($this->attributes as $key => $value) {
            if ((int) $key === $key) {
                $key = $value;
            }

            if ($this->onlyAttributes && !in_array($key, $this->onlyAttributes, true)) {
                continue;
            }

            if (!is_string($value) && is_callable($value)) {
                $data[$key] = $value($this->resource);
                continue;
            }

            if (in_array($key, $this->dateAttributes, true)) {
                $data[$key] = $this->formatDate($this[$value]);
                continue;
            }

            if (in_array($key, $this->monthAttributes, true)) {
                $data[$key] = $this->formatMonth($this[$value]);
                continue;
            }

            //            if (!key_exists($value, $this->resource->hasKey)) {
            //                throw new \Exception("Key {$value} does not exist in resource");
            //            }

            $data[$key] = $this[$value];
        }
        $this->onlyAttributes = null;

        return Arr::undot($data);
    }

    public function withOnly(?array $onlyAttributes = null): self
    {
        $this->onlyAttributes = $onlyAttributes;

        return $this;
    }

    public static function partialCollection(mixed $resources, ?array $attributes = null): Collection
    {
        return $resources->map(function ($resource) use ($attributes): self {
            return (new (static::class)($resource))->withOnly($attributes);
        });
    }

    public function formatDate($date): ?string
    {
        if (!$date) {
            return $date;
        }

        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        return $date ? $date->format('Y/m/d') : '';
    }

    public function formatMonth(string $month): ?string
    {
        if (!$month) {
            return $month;
        }

        return str_replace('-', '/', $month);
    }
}
