<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentTermRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'amount' => 'required|numeric|digits_between:1,14',
            'payment_type' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'start_date' => trans('attributes.payment_terms.start_date'),
            'end_date' => trans('attributes.payment_terms.end_date'),
            'amount' => trans('attributes.payment_terms.amount'),
            'payment_type' => trans('attributes.payment_terms.payment_type'),
        ];
    }
}
