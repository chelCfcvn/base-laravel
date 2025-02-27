<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\MPaymentTermStatus;
use App\Models\MRole;
use App\Models\PaymentTerm;
use Illuminate\Support\Facades\Auth;

class CustomerRepository extends BaseRepository
{
    public function model(): string
    {
        return Customer::class;
    }

    /**
     * @param $dataSearch
     * @return mixed
     */
    public function getCustomers($dataSearch)
    {
        $userLogin = Auth::user();
        $startDate = $dataSearch['start_date'];
        $endDate = $dataSearch['end_date'];
        $paymentTermStatus = $dataSearch['payment_term_status'];
        $status = $dataSearch['status'];

        return $this->model
            ->whereName($dataSearch['name'])
            ->whereCustomerStatus($dataSearch['customer_status'])
            ->when($userLogin->m_role_id == MRole::ADMIN, function ($q) use ($userLogin) {
                $q->whereCreatedBy($userLogin->id);
            })
            ->when(isset($startDate), function ($query) use ($startDate) {
                $query->whereHas('paymentTerms', function ($q) use ($startDate) {
                    $q->whereStartDate($startDate);
                });
            })
            ->when(isset($endDate), function ($query) use ($endDate) {
                $query->whereHas('paymentTerms', function ($q) use ($endDate) {
                    $q->whereEndDate($endDate);
                });
            })
            ->when(isset($paymentTermStatus), function ($query) use ($paymentTermStatus) {
                $query->whereHas('paymentTerms', function ($q) use ($paymentTermStatus) {
                    if ($paymentTermStatus == PaymentTerm::PAYMENT_DUE) {
                        $q->whereDate('start_date', '<=', now());
                        $q->whereDate('end_date', '>=', now());
                        $q->where('m_payment_term_status_id', MPaymentTermStatus::UNAPPROVED);
                    }

                    if ($paymentTermStatus == PaymentTerm::OVERDUE_PAYMENT) {
                        $q->whereDate('end_date', '<', now());
                        $q->wherePaymentTermStatus(MPaymentTermStatus::UNAPPROVED);
                    }
                });
            })
            ->when(isset($status), function ($query) use ($status) {
                if ($status == PaymentTerm::DUE || $status == PaymentTerm::OUT_DATE) {
                    $query->whereHas('paymentTerms', function ($q) use ($status) {
                        if ($status == PaymentTerm::DUE) {
                            $q->whereDate('start_date', '<=', now());
                            $q->whereDate('end_date', '>=', now());
                            $q->where('m_payment_term_status_id', MPaymentTermStatus::UNAPPROVED);
                        }

                        if ($status == PaymentTerm::OUT_DATE) {
                            $q->whereDate('end_date', '<', now());
                            $q->wherePaymentTermStatus(MPaymentTermStatus::UNAPPROVED);
                        }
                    });
                }

                if ($status == PaymentTerm::STANDARD) {
                    $query->whereDoesntHave('paymentTerms')
                        ->orWhereDoesntHave('paymentTerms', function ($q) {
                            $q->where('start_date', '<=', now());
                            $q->where('m_payment_term_status_id', MPaymentTermStatus::UNAPPROVED);
                        });
                }
            })
            ->when(isset($dataSearch['search_input']), function ($query) use ($dataSearch) {
                $query->where('name', 'like', '%' . $dataSearch['search_input'] . '%')
                    ->orWhere('loan_amount', $dataSearch['search_input']);
            })
            ->with('paymentTerms')
            ->orderBy('id', 'desc')
            ->paginate($dataSearch['per_page']);
    }
}
