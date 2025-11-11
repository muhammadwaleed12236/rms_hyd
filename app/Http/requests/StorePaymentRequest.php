<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sale_id' => 'required|exists:sales,id',
            'method' => 'required|in:jazz_cash,easy_paisa,bank_transfer,cash,credit_card',
            'amount' => 'required|numeric|min:0.01',
            'transaction_id' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'sale_id.required' => 'Sale is required.',
            'sale_id.exists' => 'The selected sale is invalid.',
            'method.required' => 'Payment method is required.',
            'method.in' => 'Invalid payment method selected.',
            'amount.required' => 'Amount is required.',
            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount must be at least 0.01.',
        ];
    }
}
