<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'first_name'      => 'required|string|max:100',
            'last_name'       => 'nullable|string|max:100',
            'email'           => 'required|email|unique:users,email',
            'phone'           => 'nullable|string|max:20',
            'password'        => 'required|string|min:8',
            'department_id'   => 'required|exists:departments,id',
            'designation'     => 'required|string|max:100',
            'employment_type' => 'required|in:full_time,part_time,contract,intern',
            'work_location'   => 'nullable|in:office,remote,hybrid',
            'salary'          => 'nullable|numeric|min:0',
        ];
    }
}
