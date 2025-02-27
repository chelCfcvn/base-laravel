<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'name',
        'loan_type',
        'loan_amount',
        'interest_amount',
        'date_created',
        'm_customer_status_id',
        'note',
        'created_by'
    ];

    public function paymentTerms():HasMany
    {
        return $this->hasMany(PaymentTerm::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(MRole::class, 'm_role_id', 'id');
    }

    public function scopeWhereName($query, $name)
    {
        return $name ? $query->where('name', 'LIKE', '%' . $name . '%') : $query;
    }

    public function scopeWhereCustomerStatus($query, $customerStatus)
    {
        return $customerStatus ? $query->where('m_customer_status_id', '=', $customerStatus) : $query;
    }

    public function scopeWhereCreatedBy($query, $createdBy)
    {
        return $createdBy ? $query->where('created_by', '=', $createdBy) : $query;
    }
}
