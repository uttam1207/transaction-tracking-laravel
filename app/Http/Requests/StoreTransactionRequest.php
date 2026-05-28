<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category'       => 'required|string|max:50',
            'type'           => 'required|in:debit,credit',
            'amount'         => 'required|numeric|min:0.01|max:999999999',
            'currency'       => 'required|string|size:3',
            'fee'            => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'sender_name'    => 'required|string|max:255',
            'sender_account' => 'nullable|string|max:50',
            'sender_bank'    => 'nullable|string|max:100',
            'receiver_name'  => 'required|string|max:255',
            'receiver_account' => 'nullable|string|max:50',
            'receiver_bank'  => 'nullable|string|max:100',
            'reference'      => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:10',
            'status'         => 'nullable|in:pending,processing,success,failed,cancelled',
        ];
    }
}
