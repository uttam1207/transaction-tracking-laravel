<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'question_id',
        'user_id',
        'body',
        'is_accepted',
    ];

    protected $casts = [
        'is_accepted' => 'boolean',
    ];

    /* ── Relationships ── */

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
