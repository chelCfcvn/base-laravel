<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Exceptions\InputException;
use App\Helpers\FileHelper;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;
use Maestroerror\HeicToJpg;

class FileService extends Service
{
    /**
     * @var string
     */
    protected $diskName;

    /**
     * @var Filesystem
     */
    protected $storage;

    /**
     * @return Filesystem
     */
    private function storage(): Filesystem
    {
        if (!$this->storage) {
            $this->storage = Storage::disk($this->diskName);
        }

        return $this->storage;
    }

    /**
     * Upload image
     *
     * @param  UploadedFile  $file
     * @param $type
     * @return array
     *
     * @throws InputException
     */
    public function uploadImage(UploadedFile $file, $type): array
    {
        $this->diskName = config('upload.disk');

        $fileName = FileHelper::constructFileName('', $file->getClientOriginalName());

        [$fullPath, $thumbPath] = $this->resizeImage($file, $type, $fileName);

        return ['url' => $fullPath, 'thumb' => $thumbPath, 'type' => $type];
    }

    /**
     * Fake image
     *
     * @param    $type
     * @return array
     *
     * @throws InputException
     */
    public function fakeImage($type): array
    {
        $this->diskName = config('upload.disk');
        $typeImage = config('upload.image_types' . '.' . $type);
        $imageUrl = 'https://via.placeholder.com/' . $typeImage['full_size'][0] . 'x' . $typeImage['full_size'][0] . '.png';

        $fileName = FileHelper::constructFileName();

        [$fullPath, $thumbPath] = $this->resizeImage($imageUrl, $type, $fileName);

        $imageUrl = $this->storage()->url($fullPath);
        $thumbnailUrl = $this->storage()->url($thumbPath);

        return ['url' => $imageUrl, 'thumb' => $thumbnailUrl];
    }

    /**
     * @param $data
     * @return mixed
     * @throws InputException
     */
    public function sendChat($data)
    {
        try {
            $files = $data['files'] ?? [];

            $imageUrls = [];

            foreach ($files as $file) {
                if ($file) {
                    $fileName = time() . '_' . $file->getClientOriginalName();

                    if ($file->getClientOriginalExtension() === 'heic') {
                        $fileName = time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.jpg';
                        $jpegContent = HeicToJpg::convert($file->getPathname())->get();
                        [$filePath] = $this->resizeImage($jpegContent, 'editor', $fileName);
                    } else {
                        [$filePath] = $this->resizeImage($file, 'editor', $fileName);
                    }

                    $imageUrls[] = 'storage/' . $filePath;
                }
            }

            $message = Message::query()->create([
                'message' => $data['message'] ?? '',
                'image_url' => !empty($imageUrls) ? json_encode($imageUrls) : null,
                'user_id' => Auth::user()->id,
            ]);

            $user = Auth::user();

            $appApi = config('app.app_api');
            $fullUrls = [];

            if ($message->image_url) {
                $imageUrl = json_decode($message->image_url);

                $fullUrls = array_map(function ($img) use ($appApi) {
                    return $appApi . $img;
                }, $imageUrl);
            }

            $data = [
                'id' => $message->id,
                'name' => $user->name,
                'message' => $message->message,
                'time' => Carbon::parse($message->created_at)->format('H:i'),
                'image_url' => $fullUrls,
                'user_id' => $user->id,
                'created_at' => Carbon::parse($message->created_at)->format('d/m/y'),
            ];

            broadcast(new MessageSent($data));

            $result = [
                'id' => $message->id,
                'name' => Auth::user()->name,
                'message' => $message->message,
                'time' => Carbon::parse($message->created_at)->format('H:i'),
                'image_url' => $fullUrls,
                'user_id' => $user->id,
                'created_at' => Carbon::parse($message->created_at)->format('d/m/y'),
            ];

            return $result;
        } catch (\Exception $e) {
            Log::error($e->getMessage(), [$e]);
            return false;
        }
    }

    /**
     * Resize
     *
     * @param $image
     * @param $type
     * @param $fileName
     * @return false[]|string[]
     *
     * @throws InputException
     */
    protected function resizeImage($image, $type, $fileName): array
    {
        $img = Image::make($image)->orientate();
        $typeImage = config('upload.image_types' . '.' . $type);

        if (!$typeImage) {
            throw new InputException(trans('validation.upload_error_type'));
        }

        $fullPath = FileHelper::pathUrl($fileName, config('upload.path_origin_image'));
        $thumbPath = FileHelper::pathUrl($fileName, config('upload.path_thumbnail'));

        if ($typeImage['crop']) {
            $deltaOld = $typeImage['full_size'][0] / $typeImage['full_size'][1];
            $deltaNew = $img->width() / $img->height();

            if ($deltaOld >= $deltaNew) {
                $width = $img->width();
                $height = $width / $deltaOld;
            } else {
                $height = $img->height();
                $width = $height * $deltaOld;
            }

            $img = $img->crop(intval($width), intval($height));
        }

        $imageOrigin = $img->widen($typeImage['full_size'][0], function ($constraint) {
            $constraint->upsize();
        });

        $imageThumb = clone $img;
        $imageThumb = $imageThumb->widen($typeImage['thumb_size'][0], function ($constraint) {
            $constraint->upsize();
        });

        $encodeType = config('upload.webp_ext');
        $webpQuality = config('upload.webp_quality');

        $imageOrigin = $imageOrigin->encode($encodeType, intval($webpQuality))->stream();
        $imageThumb = $imageThumb->encode($encodeType, intval($webpQuality))->stream();

        $this->storage()->put($fullPath, $imageOrigin->__toString());
        $this->storage()->put($thumbPath, $imageThumb->__toString());

        return [$fullPath, $thumbPath];
    }
}
