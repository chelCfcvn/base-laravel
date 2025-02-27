<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPaymentTermStatus extends Model
{
    use HasFactory;

    protected $table = 'm_payment_term_statuses';

    const UNAPPROVED = 1;
    const APPROVED = 2;
}
