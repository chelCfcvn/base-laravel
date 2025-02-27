<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MessageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $appApi = config('app.app_api');
        $fullUrls = [];

        if ($this->image_url) {
            $imageUrl = json_decode($this->image_url);

            $fullUrls = array_map(function ($img) use ($appApi) {
                return $appApi . $img;
            }, $imageUrl);
        }

        return [
            'id' => $this->id,
            'name' => $this->user?->name,
            'main_view' => Auth::id() == $this->user_id,
            'message' => $this->message,
            'time' => Carbon::parse($this->created_at)->format('H:i'),
            'image_url' => $fullUrls,
            'created_at' =>  Carbon::parse($this->created_at)->format('d/m/y'),
        ];
    }
}
