<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerCollection;
use App\Http\Resources\DetailCustomerResource;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    protected $customerService;

    public function __construct(
        CustomerService $customerService
    ) {
        $this->customerService = $customerService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $result = $this->customerService->getCustomers($request);

        return $this->sendSuccessResponse(new CustomerCollection($result));
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        $result = $this->customerService->edit($id);

        if ($result) {
            return $this->sendSuccessResponse(new DetailCustomerResource($result));
        }

        return $this->sendErrorResponse(trans('message.error'), []);
    }

    /**
     * @param StoreCustomerRequest $request
     * @return JsonResponse
     */
    public function store(StoreCustomerRequest $request)
    {
        $data = $request->only([
            'name',
            'loan_type',
            'loan_amount',
            'interest_amount',
            'date_created',
            'm_customer_status_id',
            'note'
        ]);

        $result = $this->customerService->store($data);

        if ($result) {
            return $this->sendSuccessResponse($result, trans('message.store.success'));
        }

        return $this->sendErrorResponse(trans('message.store.fail'), []);
    }

    /**
     * @param StoreCustomerRequest $request
     * @return JsonResponse
     */
    public function update(StoreCustomerRequest $request)
    {
        $data = $request->only([
            'name',
            'loan_type',
            'loan_amount',
            'interest_amount',
            'date_created',
            'm_customer_status_id',
            'note'
        ]);

        $customerId = $request->id;

        $result = $this->customerService->update($data, $customerId);

        if ($result) {
            return $this->sendSuccessResponse($result, trans('message.update.success'));
        }

        return $this->sendErrorResponse(trans('message.update.fail'), []);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        $result = $this->customerService->delete($id);

        if ($result) {
            return $this->sendSuccessResponse(true, trans('message.delete.success'));
        }

        return $this->sendErrorResponse(trans('message.update.fail'), []);
    }
}
