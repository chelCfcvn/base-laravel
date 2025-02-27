<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Traits\HasRateLimiter;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    use HasRateLimiter;
    public const MAX_ATTEMPTS_LOGIN = 5;
    public const DECAY_SECONDS = 60;

    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware())->except(['login']);
        $this->middleware($this->guestMiddleware())->only(['login']);
    }

    public function login(LoginRequest $request)
    {
        $ip = $request->ip();
        $inputs = $request->only(['user_name', 'password']);
        $key = Str::lower($inputs['user_name'] . '|user_login|' . $ip);
        if ($this->tooManyAttempts($key, self::MAX_ATTEMPTS_LOGIN)) {
            return $this->sendLockoutResponse($key);
        }//end if

        $loginData = AuthService::getInstance()->login($inputs);
        if ($loginData) {
            $this->clearLoginAttempts($key);

            return $this->sendSuccessResponse($loginData);
        }//end if

        $this->incrementAttempts($key, self::DECAY_SECONDS);
        if ($this->retriesLeft($key, self::MAX_ATTEMPTS_LOGIN) == 0) {
            throw new InputException(trans('auth.throttle', ['seconds' => self::DECAY_SECONDS]));
        }//end if

        return $this->sendFailedLoginResponse();
    }

    /**
     * Send Failed Login Response
     *
     * @return JsonResponse
     */
    protected function sendFailedLoginResponse(): JsonResponse
    {
        return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_UNAUTHORIZED, trans('auth.failed'), null);
    }

    /**
     * Logout
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $currentUser = Auth::user();
        $currentUser->currentAccessToken()->delete();

        return $this->sendSuccessResponse(null, trans('auth.logout_success'));
    }

    /**
     * @return JsonResponse
     */
    public function currentLoginUser()
    {
        $currentUser = $this->guard()->user();

        return $this->sendSuccessResponse($currentUser);
    }

    /**
     * Change password
     *
     * @param ChangePasswordRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $currentUser = Auth::user();
        $inputs = $request->only(['current_password', 'password']);
        $data = AuthService::getInstance()->withUser($currentUser)->changePassword($inputs);

        return $this->sendSuccessResponse($data, trans('message.update.success'));
    }
}
