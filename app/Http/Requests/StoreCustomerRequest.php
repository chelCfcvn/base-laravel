<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'name' => 'required|string|max:50',
            'loan_type' => 'required|string|max:255',
            'loan_amount' => 'required|numeric|digits_between:1,14',
            'interest_amount' => 'required|numeric|digits_between:1,14',
            'date_created' => 'required|date_format:Y-m-d',
            'm_customer_status_id' => 'required|integer|exists:m_customer_statuses,id',
            'note' => 'nullable'
        ];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'name' => trans('attributes.customers.name'),
            'loan_type' => trans('attributes.customers.loan_type'),
            'loan_amount' => trans('attributes.customers.loan_amount'),
            'interest_amount' => trans('attributes.customers.interest_amount'),
            'date_created' => trans('attributes.customers.date_created'),
            'm_customer_status_id' => trans('attributes.customers.status'),
            'note' => trans('attributes.customers.note')
        ];
    }
}
