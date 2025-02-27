<?php

namespace Database\Seeders;

use App\Enums\RoleUserEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users[] = [
            'id' => 1,
            'name' => 'super_admin',
            'email' => 'super-admin@domain.com',
            'password' => Hash::make('123456xX@'),
            'm_role_id' => RoleUserEnum::SUPER_ADMIN->value,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];

        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'id' => $i + 1,
                'name' => "admin_{$i}",
                'email' => "admin{$i}@domain.com",
                'password' => Hash::make('123456xX@'),
                'm_role_id' => RoleUserEnum::ADMIN->value,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

        User::query()->truncate();
        User::query()->insert($users);
    }
}
