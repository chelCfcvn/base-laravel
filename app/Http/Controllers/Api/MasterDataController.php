<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\MasterDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MasterDataController extends BaseController
{
    /**
     * MasterDataController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware())->except('show');
    }

    /**
     * Master data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $resources = $request->get('resources');

        if (!is_array($resources)) {
            return $this->sendSuccessResponse([]);
        }//end if

        $data = MasterDataService::getInstance()->withResources($resources)->get();

        return $this->sendSuccessResponse($data);
    }
}
