<?php

namespace Database\Seeders;

use App\Models\MPaymentTermStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MPaymentTermStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MPaymentTermStatus::query()->truncate();

        $data = [
            ["name" => "Chưa duyệt"],
            ["name" => "Đã duyệt"],
        ];

        MPaymentTermStatus::query()->insert($data);
    }
}
