<?php

namespace Database\Seeders;

use App\Models\MCustomerStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MCustomerStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MCustomerStatus::query()->truncate();

        $data = [
            ["name" => "Đang giao dịch"],
            ["name" => "Đã giao dịch"],
        ];

        MCustomerStatus::query()->insert($data);
    }
}
