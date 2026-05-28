<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'employee_id'          => $this->employee_id,
            'full_name'            => $this->full_name,
            'email'                => $this->email,
            'designation'          => $this->designation,
            'employment_type'      => $this->employment_type,
            'work_location'        => $this->work_location,
            'status'               => $this->status,
            'performance_score'    => $this->performance_score,
            'annual_leave_balance' => $this->annual_leave_balance,
            'sick_leave_balance'   => $this->sick_leave_balance,
            'is_checked_in'        => $this->is_checked_in,
            'today_work_hours'     => $this->today_work_hours,
            'department'           => $this->whenLoaded('department', fn() => [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ]),
            'manager' => $this->whenLoaded('manager', fn() => [
                'id'       => $this->manager?->id,
                'full_name' => $this->manager?->full_name,
            ]),
        ];
    }
}
