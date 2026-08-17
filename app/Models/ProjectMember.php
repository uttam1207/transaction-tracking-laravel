<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    protected $fillable = [
        'project_id', 'employee_id', 'role', 'is_manager', 'joined_at',
    ];

    protected $casts = [
        'is_manager' => 'boolean',
        'joined_at'  => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
