<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\CustomerService;
use App\Services\StatisticService;
use Illuminate\Http\Request;

class StatisticController extends BaseController
{
    protected $statisticService;

    public function __construct(
        StatisticService $statisticService
    ) {
        $this->statisticService = $statisticService;
    }

    public function index(Request $request)
    {
        $statistics = $this->statisticService->statistic();

        return $this->sendSuccessResponse($statistics);
    }

    public function getProfitAmount(Request $request)
    {
        $profitAmount = $this->statisticService->getProfitAmount($request);

        return $this->sendSuccessResponse(['profitAmount' => $profitAmount]);
    }
}
