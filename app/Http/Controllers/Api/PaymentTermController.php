<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StorePaymentTermRequest;
use App\Http\Resources\DetailPaymentTermResource;
use App\Http\Resources\PaymentTermsResource;
use App\Models\MPaymentTermStatus;
use App\Services\PaymentTermService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentTermController extends BaseController
{
    protected $paymentTermService;

    public function __construct(
        PaymentTermService $paymentTermService
    ) {
        $this->paymentTermService = $paymentTermService;
    }

    /**
     * @param $customerId
     * @return JsonResponse
     */
    public function index($customerId)
    {
        $result = $this->paymentTermService->getPaymentTerms($customerId);

        if ($result) {
            return $this->sendSuccessResponse(PaymentTermsResource::collection($result));
        }

        return $this->sendErrorResponse(trans('message.error'), []);
    }

    /**
     * @param StorePaymentTermRequest $request
     * @return JsonResponse
     */
    public function store(StorePaymentTermRequest $request)
    {
        $data = $request->only([
            'start_date',
            'end_date',
            'amount',
            'payment_type',
        ]);

        $customerId = $request->customer_id;

        $result = $this->paymentTermService->store($data, $customerId);

        if ($result) {
            return $this->sendSuccessResponse($result, trans('message.store.success'));
        }

        return $this->sendErrorResponse(trans('message.store.fail'), []);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function edit($id)
    {
        $result = $this->paymentTermService->edit($id);

        if ($result) {
            return $this->sendSuccessResponse(new DetailPaymentTermResource($result));
        }

        return $this->sendErrorResponse(trans('message.error'), []);
    }

    /**
     * @param StorePaymentTermRequest $request
     * @return JsonResponse
     */
    public function update(StorePaymentTermRequest $request)
    {
        $data = $request->only([
            'start_date',
            'end_date',
            'amount',
            'payment_type',
        ]);

        $paymentTermId = $request->id;

        $result = $this->paymentTermService->update($data, $paymentTermId);

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
        $result = $this->paymentTermService->delete($id);

        if ($result) {
            return $this->sendSuccessResponse(true, trans('message.delete.success'));
        }

        return $this->sendErrorResponse(trans('message.update.fail'), []);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateStatus(Request $request)
    {
        $paymentTermId = $request->get('id');
        $status = $request->get('status');
        $result = $this->paymentTermService->updateStatus($paymentTermId, $status);

        if ($result && $status == MPaymentTermStatus::APPROVED) {
            return $this->sendSuccessResponse(true, trans('message.approve.success'));
        } elseif ($result && $status == MPaymentTermStatus::UNAPPROVED) {
            return $this->sendSuccessResponse(true, trans('message.un_approve.success'));
        }

        return $this->sendErrorResponse(trans('message.error'), []);
    }
}
