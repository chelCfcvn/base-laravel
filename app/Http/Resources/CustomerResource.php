<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'loan_type' => $this->loan_type,
            'loan_amount' => (int) $this->loan_amount,
            'interest_amount' => (int) $this->interest_amount,
            'm_customer_status_id' => $this->m_customer_status_id,
            'date_created' => $this->date_created,
            'note' => $this->note,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'payment_terms' => PaymentTermResource::collection($this->paymentTerms),
        ];
    }
}
