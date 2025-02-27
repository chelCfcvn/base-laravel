<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentTerm extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'payment_terms';

    protected $fillable = [
        'customer_id',
        'start_date',
        'end_date',
        'amount',
        'payment_type',
        'm_payment_term_status_id',
        'approve_date'
    ];

    const PAYMENT_DUE = 1;

    const OVERDUE_PAYMENT = 2;

    const STANDARD = 1;
    const DUE = 2;
    const OUT_DATE = 3;

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(MPaymentTermStatus::class, 'm_payment_term_status_id', 'id');
    }

    public function scopeWhereCustomerId($query, $customerId)
    {
        return $customerId ? $query->where('customer_id', '=', $customerId) : $query;
    }

    public function scopeWhereStartDate($query, $startDate)
    {
        return $startDate ? $query->whereDate('start_date', '>=', Carbon::createFromFormat('d/m/Y', $startDate)->format('Y-m-d')) : $query;
    }

    public function scopeWhereEndDate($query, $endDate)
    {
        return $endDate ? $query->whereDate('end_date', '<=', Carbon::createFromFormat('d/m/Y', $endDate)->format('Y-m-d')) : $query;
    }

    public function scopeWherePaymentTermStatus($query, $paymentTermStatus)
    {
        return $paymentTermStatus ? $query->where('m_payment_term_status_id', '=', $paymentTermStatus) : $query;
    }
}
