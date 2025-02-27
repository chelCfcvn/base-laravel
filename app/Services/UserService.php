<?php

namespace App\Services;

use App\Enums\RoleUserEnum;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class UserService extends Service
{
    public function list(array $condition)
    {
        return User::query()
            ->whereNot('m_role_id', RoleUserEnum::SUPER_ADMIN->value)
            ->when(Arr::has($condition, 'search_input'), function ($query) use ($condition) {
                $query->where('name', 'like', '%' . $condition['search_input'] . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate($condition['per_page'] ?? 10);
    }

    public function store(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['m_role_id'] = RoleUserEnum::ADMIN->value;

        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return tap($user)->update($data);
    }

    public function destroy(User $user): bool
    {
        return $user->delete();
    }

    public function updateStatus(array $data, User $user)
    {
        return tap($user)->update($data);
    }
}
