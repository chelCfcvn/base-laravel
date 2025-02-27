<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Http\Resources\MessageResource;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maestroerror\HeicToJpg;

class MessageService extends Service
{
    public function index(array $params)
    {
        $messages = Message::query()
            ->with('user')
            ->orderBy('id', 'DESC')
            ->paginate($params['per_page'] ?? PAGINATE);

        $groupedMessages = $messages->groupBy(function ($message) {
            return Carbon::parse($message->created_at)->format('d/m/Y');
        })->map(function ($items) {
            return MessageResource::collection($items->sortBy('created_at'));
        });

        return [
            'data' => MessageResource::collection($messages->sortBy('id')),
            'per_page' => $messages->perPage(),
            'total_page' => $messages->lastPage(),
            'current_page' => $messages->currentPage(),
            'total' => $messages->total(),
        ];
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function store(array $data)
    {
        $files = $data['files'] ?? [];

        $imageUrls = [];

        foreach ($files as $file) {
            if ($file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = 'images_chat/' . $fileName;

                if ($file->getClientOriginalExtension() === 'heic') {
                    $fileName = time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.jpg';
                    $filePath = 'images_chat/' . $fileName;

                    $jpegContent = HeicToJpg::convert($file->getPathname())->get();

                    Storage::disk('public')->put($filePath, $jpegContent);
                } else {
                    Storage::disk('public')->put($filePath, file_get_contents($file));
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
            'created_at' => Carbon::parse($message->created_at)->format('y/m/d'),
        ];

        broadcast(new MessageSent($data));

        return $message;
    }
}
