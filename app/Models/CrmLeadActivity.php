<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmLeadActivity extends Model
{
    protected $fillable = [
        'lead_id', 'type', 'subject', 'description', 'activity_at', 'created_by',
    ];

    protected $casts = [
        'activity_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(CrmLead::class, 'lead_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'call'     => 'bi-telephone',
            'email'    => 'bi-envelope',
            'meeting'  => 'bi-people',
            'note'     => 'bi-sticky',
            'demo'     => 'bi-display',
            'proposal' => 'bi-file-earmark-text',
            default    => 'bi-activity',
        };
    }
}
