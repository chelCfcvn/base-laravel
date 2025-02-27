<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get the guest middleware for the application.
     *
     * @return string
     */
    public function guestMiddleware(): string
    {
        $guard = $this->getGuard();

        return $guard ? ('guest:' . $guard) : 'guest';
    }

    /**
     * Get the auth middleware for the application.
     *
     * @return string
     */
    public function authMiddleware(): string
    {
        $guard = $this->getGuard();

        return $guard ? ('auth:' . $guard) : 'auth';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return string
     */
    protected function getGuard(): string
    {
        return property_exists($this, 'guard') ? $this->guard : config('auth.defaults.guard');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return StatefulGuard
     */
    protected function guard(): StatefulGuard|RequestGuard
    {
        return Auth::guard($this->getGuard());
    }

    /**
     * Send Error Response
     *
     * @param  string  $message
     * @param  null  $errors
     * @param  null  $data
     * @param  int  $code
     * @return JsonResponse
     */
    protected function sendErrorResponse(string $message, $errors = null, $data = null, int $code = ResponseHelper::STATUS_CODE_BAD_REQUEST): JsonResponse
    {
        return ResponseHelper::sendResponse($code, $message, $data, $errors);
    }

    /**
     * @param $data
     * @param  string  $message
     * @param  int  $code
     * @return JsonResponse
     */
    protected function sendSuccessResponse($data, string $message = '', int $code = ResponseHelper::STATUS_CODE_SUCCESS): JsonResponse
    {
        return ResponseHelper::sendResponse($code, $message, $data);
    }

    protected function sendSuccessMessageResponse(string $message = '', int $code = ResponseHelper::STATUS_CODE_SUCCESS): JsonResponse
    {
        return ResponseHelper::sendResponse($code, $message);
    }

    /**
     * Convert Request
     *
     * @param $request
     * @return array
     */
    protected function convertRequest($request): array
    {
        return [
            $request->get('search'),
            $request->get('orders'),
            $request->get('filters'),
            $request->get('per_page'),
        ];
    }
}
