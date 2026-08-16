<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Branch;
use App\Models\User;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Team;
use App\Models\CostCenter;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['branch_id', 'name', 'code', 'description', 'manager_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function costCenters()
    {
        return $this->hasMany(CostCenter::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getEmployeeCountAttribute(): int
    {
        return $this->employees()->count();
    }
}
