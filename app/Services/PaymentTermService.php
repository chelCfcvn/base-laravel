<?php

namespace App\Services;

use App\Models\MCustomerStatus;
use App\Models\MPaymentTermStatus;
use App\Models\MRole;
use App\Repositories\CustomerRepository;
use App\Repositories\PaymentTermRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentTermService extends Service
{
    protected $paymentTermRepository;
    protected $userRepository;
    protected $customerRepository;

    public function __construct(
        PaymentTermRepository $paymentTermRepository,
        UserRepository $userRepository,
        CustomerRepository $customerRepository
    ) {
        $this->paymentTermRepository = $paymentTermRepository;
        $this->userRepository = $userRepository;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getPaymentTerms($customerId)
    {
        $customer = $this->customerRepository->find($customerId);

        if (!$customer) {
            return false;
        }

        if (!$this->checkPermissionsExecutionPaymentTerm($customer)) {
            return false;
        }

        return $this->paymentTermRepository->getPaymentTerms($customerId);
    }

    /**
     * @param $data
     * @param $customerId
     * @return false
     */
    public function store($data, $customerId)
    {
        try {
            $customer = $this->customerRepository->find($customerId);

            if ($customer && $customer->paymentTerms->count() >= 3) {
                return false;
            }

            if (!$this->checkPermissionsExecutionPaymentTerm($customer)) {
                return false;
            }

            if ($customer->m_customer_status_id == MCustomerStatus::TRADED) {
                return false;
            }

            $dataCreate = array_merge($data,[
                'customer_id' => $customerId,
                'm_payment_term_status_id' => MPaymentTermStatus::UNAPPROVED
            ]);

            return $this->paymentTermRepository->create($dataCreate);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
            return false;
        }
    }

    /**
     * @param $id
     * @return false|mixed
     */
    public function edit($id)
    {
        $paymentTerm = $this->paymentTermRepository->find($id);

        if (!$paymentTerm) {
            return false;
        }

        if (!$this->checkPermissionsUpdatePaymentTerm($paymentTerm)) {
            return false;
        }

        return $paymentTerm;
    }

    /**
     * @param $data
     * @param $paymentTermId
     * @return false
     */
    public function update($data, $paymentTermId)
    {
        try {
            $paymentTerm = $this->paymentTermRepository->find($paymentTermId);

            if (!$paymentTerm) {
                return false;
            }

            if (!$this->checkPermissionsUpdatePaymentTerm($paymentTerm)) {
                return false;
            }

            return $paymentTerm->update($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
            return false;
        }
    }

    /**
     * @param $paymentTerm
     * @return bool
     */
    public function checkPermissionsUpdatePaymentTerm($paymentTerm)
    {
        $userLogin = Auth::user();

        if ($userLogin->m_role_id == MRole::ADMIN && $paymentTerm->m_payment_term_status_id == MPaymentTermStatus::APPROVED) {
            return false;
        }

        return true;
    }

    /**
     * @param $customer
     * @return bool
     */
    public function checkPermissionsExecutionPaymentTerm($customer)
    {
        $userLogin = Auth::user();

        if ($userLogin->m_role_id == MRole::ADMIN && $customer->created_by != $userLogin->id) {
            return false;
        }

        return true;
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        try {
            $paymentTerm = $this->paymentTermRepository->find($id);

            if (!$paymentTerm) {
                return false;
            }

            if (!$this->checkPermissionsDeletePaymentTerm($paymentTerm)) {
                return false;
            }

            $paymentTerm->delete();

            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
            return false;
        }
    }

    /**
     * @param $paymentTerm
     * @return bool
     */
    public function checkPermissionsDeletePaymentTerm($paymentTerm)
    {
        $userLogin = Auth::user();

        $customer = $this->customerRepository->find($paymentTerm->customer_id);

        if (!$customer) {
            return false;
        }

        if ($userLogin->m_role_id == MRole::ADMIN && $paymentTerm->m_payment_term_status_id == MPaymentTermStatus::APPROVED) {
            return false;
        }

        if ($userLogin->m_role_id == MRole::ADMIN && $paymentTerm->m_payment_term_status_id == MPaymentTermStatus::UNAPPROVED && $userLogin->id != $customer->created_by) {
            return false;
        }

        return true;
    }

    /**
     * @param $id
     * @param $status
     * @return false
     */
    public function updateStatus($id, $status)
    {
        try {
            $userLogin = Auth::user();
            $paymentTerm = $this->paymentTermRepository->find($id);

            if (!$paymentTerm) {
                return false;
            }

            if ($userLogin->m_role_id == MRole::ADMIN) {
                return false;
            }

            $customer = $this->customerRepository->find($paymentTerm->customer_id);

            if (!$customer || $customer->m_customer_status_id == MCustomerStatus::TRADED) {
                return false;
            }

            if ($status == MPaymentTermStatus::UNAPPROVED) {
                return $paymentTerm->update([
                    'm_payment_term_status_id' => MPaymentTermStatus::UNAPPROVED,
                    'approve_date' => null
                ]);
            } else if ($status == MPaymentTermStatus::APPROVED) {
                return $paymentTerm->update([
                    'm_payment_term_status_id' => MPaymentTermStatus::APPROVED,
                    'approve_date' => now()
                ]);
            }

            return false;
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
            return false;
        }
    }
}
