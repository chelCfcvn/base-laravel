<?php

namespace App\Http\Resources;

use App\Models\MPaymentTermStatus;
use App\Models\MRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class PaymentTermsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource;
        $canDelete = true;
        $canUpdate = true;
        $canApprove = false;
        $canUnApprove = false;
        $userLogin = Auth::user();

        if ($userLogin->m_role_id == MRole::ADMIN && $data->m_payment_term_status_id == MPaymentTermStatus::APPROVED) {
            $canDelete = false;
            $canUpdate = false;
        }

        if ($userLogin->m_role_id == MRole::SUPER_ADMIN && $data->m_payment_term_status_id == MPaymentTermStatus::UNAPPROVED) {
            $canApprove = true;
        }

        if ($userLogin->m_role_id == MRole::SUPER_ADMIN && $data->m_payment_term_status_id == MPaymentTermStatus::APPROVED) {
            $canUnApprove = true;
        }

        return [
            'id' => $data->id,
            'customer_id' => $data->customer_id,
            'start_date' => $data->start_date,
            'end_date' => $data->end_date,
            'amount' => $data->amount,
            'payment_type' => $data->payment_type,
            'm_payment_term_status_id' => $data->m_payment_term_status_id,
            'approve_date' => $data->approve_date,
            'can_delete' => $canDelete,
            'can_approve' => $canApprove,
            'can_un_approve' => $canUnApprove,
            'can_update' => $canUpdate
        ];
    }
}
