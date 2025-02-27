<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MRole extends Model
{
    use HasFactory;

    protected $table = 'm_roles';

    const SUPER_ADMIN = 1;
    const ADMIN = 2;
}
