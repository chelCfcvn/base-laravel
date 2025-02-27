<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\InputException;
use App\Http\Controllers\BaseController;
use App\Http\Requests\UploadImageRequest;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;

class UploadImageController extends BaseController
{
    /**
     * UploadController constructor.
     */
    public function __construct()
    {
        $this->middleware($this->authMiddleware())->except('upload');
    }

    /**
     * Upload
     *
     * @param UploadImageRequest $request
     * @return JsonResponse
     * @throws InputException
     */
    public function upload(UploadImageRequest $request): JsonResponse
    {
        $data = FileService::getInstance()->uploadImage($request->file('image'), $request->get('type'));

        return $this->sendSuccessResponse($data);
    }
}
