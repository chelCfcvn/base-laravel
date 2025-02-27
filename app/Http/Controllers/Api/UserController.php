<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\UserColection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $data = UserService::getInstance()->list($request->all());

        return $this->sendSuccessResponse(new UserColection($data));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $data = UserService::getInstance()->store($request->all());

        return $this->sendSuccessResponse(new UserResource($data));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        return $this->sendSuccessResponse(new UserResource($user));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, User $user)
    {
        $data = UserService::getInstance()->update($user, $request->all());

        return $this->sendSuccessResponse(new UserResource($data));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        UserService::getInstance()->destroy($user);

        return $this->sendSuccessResponse(null);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $data = UserService::getInstance()->updateStatus($request->all(), $user);

        return $this->sendSuccessResponse(new UserResource($data));
    }
}
