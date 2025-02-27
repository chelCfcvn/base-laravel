<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MCustomerStatus extends Model
{
    use HasFactory;

    protected $table = 'm_customer_statuses';

    const TRADING = 1;
    const TRADED = 2;
}
