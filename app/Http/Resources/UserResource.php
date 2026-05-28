<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'username'          => $this->username,
            'phone'             => $this->phone,
            'avatar_url'        => $this->avatar_url,
            'status'            => $this->status,
            'role'              => $this->getRoleNames()->first(),
            'is_online'         => (bool) $this->is_online,
            'last_login_at'     => $this->last_login_at?->toIso8601String(),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at'        => $this->created_at->toIso8601String(),
            'department'        => $this->whenLoaded('department', fn() => [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ]),
            'employee'          => $this->whenLoaded('employee', fn() => [
                'id'          => $this->employee->id,
                'employee_id' => $this->employee->employee_id,
                'designation' => $this->employee->designation,
            ]),
        ];
    }
}
