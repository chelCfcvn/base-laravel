<?php

namespace Database\Seeders;

use App\Models\MRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MRole::query()->truncate();

        $data = [
            ["name" => "Super Admin"],
            ["name" => "Admin"],
        ];

        MRole::query()->insert($data);
    }
}
