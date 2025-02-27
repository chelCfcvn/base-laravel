<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InputException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\MessageRequest;
use App\Services\FileService;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseController
{
    /**
     * MessageController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware());
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) : JsonResponse
    {
        $data = MessageService::getInstance()->index($request->all());

        if ($data) {
            return $this->sendSuccessResponse($data);
        }

        return $this->sendErrorResponse(trans('message.error'), []);
    }

    /**
     * @param MessageRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function store(MessageRequest $request): JsonResponse
    {
        $data = $request->only([
            'message',
            'files'
        ]);

        $data = FileService::getInstance()->sendChat($data);

        if ($data) {
            return $this->sendSuccessResponse($data);
        }

        return $this->sendErrorResponse(trans('message.error'), []);
    }
}
