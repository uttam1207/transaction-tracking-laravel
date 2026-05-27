<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait HasAuditLog
{
    public static function bootHasAuditLog(): void
    {
        static::created(function ($model) {
            $model->createAuditLog('created', null, $model->toArray());
        });

        static::updated(function ($model) {
            $model->createAuditLog('updated', $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            $model->createAuditLog('deleted', $model->toArray(), null);
        });
    }

    protected function createAuditLog(string $event, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'module' => class_basename(get_class($this)),
        ]);
    }
}
