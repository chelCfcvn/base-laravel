<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailCustomerResource extends JsonResource
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
            'name' => $data->name,
            'loan_type' => $data->loan_type,
            'loan_amount' => (int) $data->loan_amount,
            'interest_amount' => (int) $data->interest_amount,
            'm_customer_status_id' => $data->m_customer_status_id,
            'date_created' => $data->date_created,
            'note' => $data->note,
            'created_by' => $data->created_by,
            'created_at' => $data->created_at
        ];
    }
}
