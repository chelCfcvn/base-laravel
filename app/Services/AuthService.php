<?php

namespace App\Services;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthService extends Service
{
    /**
     * Login
     *
     * @param  array  $data
     * @return array|null
     */
    public function login(array $data): ?array
    {
        $user = User::query()->where('name', $data['user_name'])->first();

        if (!$user || !Hash::check($data['password'], $user->password) || $user->active == User::INACTIVE) {
            throw new InputException(trans('auth.login_fail'));
        }

        $token = $user->createToken('authUserToken')->plainTextToken;

        return [
            'access_token' => $token,
            'type_token' => 'Bearer',
        ];
    }

    /**
     * Change Password
     *
     * @param  array  $data
     * @return bool
     *
     * @throws InputException
     */
    public function changePassword(array $data): bool
    {
        $user = $this->user;
        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return true;
    }
}
