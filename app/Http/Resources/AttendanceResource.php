<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'date'           => $this->date,
            'check_in'       => $this->check_in,
            'check_out'      => $this->check_out,
            'work_hours'     => $this->work_hours ? round($this->work_hours, 2) : null,
            'overtime_hours' => $this->overtime_hours ? round($this->overtime_hours, 2) : null,
            'status'         => $this->status,
            'check_in_ip'    => $this->check_in_ip,
            'approved'       => (bool) $this->approved,
            'employee'       => $this->whenLoaded('employee', fn() => [
                'id'          => $this->employee->id,
                'employee_id' => $this->employee->employee_id,
                'full_name'   => $this->employee->full_name,
            ]),
        ];
    }
}
