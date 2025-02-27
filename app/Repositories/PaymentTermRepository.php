<?php

namespace App\Repositories;

use App\Models\MPaymentTermStatus;
use App\Models\PaymentTerm;

class PaymentTermRepository extends BaseRepository
{
    public function model(): string
    {
        return PaymentTerm::class;
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function deleteByCustomerId($customerId)
    {
        return $this->model->whereCustomerId($customerId)->delete();
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function getPaymentTerms($customerId)
    {
        return $this->model
            ->whereCustomerId($customerId)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function checkExistsPaymentUnApprove($customerId)
    {
        return $this->model
            ->whereCustomerId($customerId)
            ->wherePaymentTermStatus(MPaymentTermStatus::UNAPPROVED)
            ->exists();
    }
}
