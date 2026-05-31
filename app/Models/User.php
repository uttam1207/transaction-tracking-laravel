<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Employee;
use App\Models\Wallet;
use App\Models\Department;
use App\Models\LoginHistory;
use App\Models\AppNotification;
use App\Models\AuditLog;
use App\Models\ActivityLog;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name', 'username', 'email', 'phone', 'password', 'avatar', 'status',
        'role', 'department_id', 'timezone', 'language', 'theme',
        'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes',
        'otp_code', 'otp_expires_at', 'phone_verified_at', 'last_login_at',
        'last_login_ip', 'is_online',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes', 'otp_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'otp_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
        'two_factor_enabled' => 'boolean',
        'is_online' => 'boolean',
        'two_factor_recovery_codes' => 'array',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function appNotifications()
    {
        return $this->hasMany(AppNotification::class);
    }

    public function unreadNotifications()
    {
        return $this->hasMany(AppNotification::class)->where('is_read', false);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar
            ? asset('storage/' . $this->avatar)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function getDashboardRoute(): string
    {
        return match($this->role) {
            'super_admin', 'admin' => route('admin.dashboard'),
            'manager' => route('admin.dashboard'),
            'employee' => route('employee.dashboard'),
            default => route('admin.dashboard'),
        };
    }
}
