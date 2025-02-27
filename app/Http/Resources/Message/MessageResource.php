<?php

namespace App\Http\Resources\Message;

use App\Http\Resources\BaseResource;

class MessageResource extends BaseResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function initAttributes(): array
    {
        return [
            'id',
            'message',
            'image_url',
            'created_at',
            'user_name' => fn($resource) => $resource?->user?->name,
        ];
    }
}
