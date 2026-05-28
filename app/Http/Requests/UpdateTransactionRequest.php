<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'category'       => 'sometimes|required|string|max:50',
            'status'         => 'sometimes|required|in:pending,processing,success,failed,cancelled,blocked',
            'amount'         => 'sometimes|required|numeric|min:0.01',
            'fee'            => 'nullable|numeric|min:0',
            'sender_name'    => 'sometimes|required|string|max:255',
            'receiver_name'  => 'sometimes|required|string|max:255',
        ];
    }
}
