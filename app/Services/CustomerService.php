<?php

namespace App\Services;

use App\Exceptions\InputException;
use App\Models\MCustomerStatus;
use App\Models\MPaymentTermStatus;
use App\Models\MRole;
use App\Repositories\CustomerRepository;
use App\Repositories\PaymentTermRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerService extends Service
{
    protected $customerRepository;
    protected $userRepository;
    protected $paymentTermRepository;

    public function __construct(
        CustomerRepository $customerRepository,
        UserRepository $userRepository,
        PaymentTermRepository $paymentTermRepository
    ) {
        $this->customerRepository = $customerRepository;
        $this->userRepository     = $userRepository;
        $this->paymentTermRepository = $paymentTermRepository;
    }

    /**
     * @param $request
     * @return mixed
     */
    public function getCustomers($request)
    {
        $dataSearch['name'] = $request->get('name');
        $dataSearch['start_date'] = $request->get('start_date');
        $dataSearch['end_date'] = $request->get('end_date');
        $dataSearch['payment_term_status'] = $request->get('payment_term_status');
        $dataSearch['customer_status'] = $request->get('m_customer_status_id');
        $dataSearch['per_page'] = $request->get('per_page');
        $dataSearch['search_input'] = $request->get('search_input');
        $dataSearch['status'] = $request->get('status');

        return $this->customerRepository->getCustomers($dataSearch);
    }

    /**
     * @param $id
     * @return false|mixed
     */
    public function edit($id)
    {
        $customer = $this->customerRepository->find($id);

        if (!$customer) {
            return false;
        }

        if (!$this->checkPermissionsExecutionCustomer($customer)) {
            return false;
        }

        return $customer;
    }

    /**
     * @param $data
     * @return false
     */
    public function store($data)
    {
        try {
            $dataCreate = array_merge($data,[
                'created_by' => Auth::user()->id
            ]);

            return $this->customerRepository->create($dataCreate);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
            return false;
        }
    }

    /**
     * @param $data
     * @param $customerId
     * @return false
     * @throws InputException
     */
    public function update($data, $customerId)
    {
        $customer = $this->customerRepository->find($customerId);

        if (!$customer) {
            return false;
        }

        if (!$this->checkPermissionsExecutionCustomer($customer)) {
            return false;
        }

        $paymentTermOfCustomer = $this->paymentTermRepository->checkExistsPaymentUnApprove($customerId);

        if ($data['m_customer_status_id'] == MCustomerStatus::TRADED && $paymentTermOfCustomer) {
            throw new InputException(trans('message.error_update_customer_status'));
        }

        try {
            return $customer->update($data);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
            return false;
        }
    }

    /**
     * @param $customer
     * @return bool
     */
    public function checkPermissionsExecutionCustomer($customer)
    {
        $userLogin = Auth::user();

        if ($userLogin->m_role_id == MRole::ADMIN && $customer->created_by != $userLogin->id) {
            return false;
        }

        return true;
    }

    /**
     * @param $id
     * @return false|mixed
     */
    public function delete($id)
    {
        try {
            $customer = $this->customerRepository->find($id);

            if (!$customer) {
                return false;
            }

            if (!$this->checkPermissionsExecutionCustomer($customer)) {
                return false;
            }

            DB::beginTransaction();

            $customer->delete();
            $this->paymentTermRepository->deleteByCustomerId($id);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), [$e]);
            return false;
        }
    }
}
