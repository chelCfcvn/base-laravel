<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentTermResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'amount' => (int) $this->amount,
            'payment_type' => $this->payment_type,
            'm_payment_term_status_id' => $this->m_payment_term_status_id,
            'created_at' => $this->created_at
        ];
    }
}
