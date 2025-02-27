<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailPaymentTermResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'id' => $data->id,
            'customer_id' => $data->customer_id,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'amount' => (int) $data->amount,
            'payment_type' => $data->payment_type,
            'm_payment_term_status_id' => $data->m_payment_term_status_id,
            'approve_date' => $data->approve_date,
            'created_at' => $data->created_at
        ];
    }
}
