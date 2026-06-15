<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Answer;
use App\Models\User;

class Question extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'body',
        'status',
        'is_pinned',
        'views',
    ];

    /**
     * Use slug as the route key so URLs are /qa/what-is-dairy instead of /qa/1
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $casts = [
        'is_pinned' => 'boolean',
        'views'     => 'integer',
    ];

    /* ── Relationships ── */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    
    
    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class)->orderBy('is_accepted', 'desc')->orderBy('created_at');
    }

    public function acceptedAnswer(): HasOne
    {
        return $this->hasOne(Answer::class)->where('is_accepted', true);
    }

    /* ── Permission helpers ── */

    public function canEdit(User $user): bool
    {
        return $user->isSuperAdmin() || $this->user_id === $user->id;
    }

    public function canDelete(User $user): bool
    {
        return $user->isSuperAdmin() || $this->user_id === $user->id;
    }

    /* ── Scopes ── */

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /* ── Helpers ── */

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'open'     => '<span class="badge bg-success-subtle text-success border border-success-subtle">Open</span>',
            'resolved' => '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">Resolved</span>',
            'closed'   => '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Closed</span>',
            default    => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
