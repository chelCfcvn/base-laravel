<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\MCustomerStatus;
use App\Models\MPaymentTermStatus;
use App\Models\PaymentTerm;
use Carbon\Carbon;

class StatisticService extends Service
{
    public function statistic()
    {
        $customerActives = Customer::query()->where('m_customer_status_id', MCustomerStatus::TRADING)
            ->get();

        $customerActiveCount = $customerActives->count();
        $customerOriginAmount = $customerActives->sum('loan_amount');
        $standardCustomerCount = Customer::query()->where('m_customer_status_id', MCustomerStatus::TRADING)
            ->whereDoesntHave('paymentTerms')
            ->orWhereDoesntHave('paymentTerms', function ($query) {
                $query->where('start_date', '<=', now());
            })
            ->count();

        $overdueCustomerCount = Customer::query()->where('m_customer_status_id', MCustomerStatus::TRADING)
            ->whereHas('paymentTerms', function ($query) {
                $query->where('m_payment_term_status_id', MPaymentTermStatus::UNAPPROVED)
                    ->whereDate('end_date', '<', now());
            })->count();

        return [
            'customerActiveCount' => $customerActiveCount,
            'customerOriginAmount' => $customerOriginAmount,
            'profitAmount' => $this->getProfitAmount(),
            'standardCustomerCount' => $standardCustomerCount,
            'overdueCustomerCount' => $overdueCustomerCount,
        ];
    }

    public function getProfitAmount($data = null)
    {
        if (!$data) {
            $startDate = Carbon::now()->subMonth(1)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
        } else {
            $startDate = $data->start_date;
            $endDate = $data->end_date;
        }

        $profit = PaymentTerm::query()
            ->where('m_payment_term_status_id', MPaymentTermStatus::APPROVED)
            ->whereDate('approve_date', '>=', $startDate)
            ->whereDate('approve_date', '<=', $endDate)
            ->get()
            ->sum('amount');

        return $profit;
    }
}
