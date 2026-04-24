<?php

namespace App\Models;

use App\Enums\TicketStatus;
use App\Services\TicketVisibilityScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number', 'reporter_id', 'handler_id', 'category_id', 'priority_id',
        'title', 'description', 'status', 'started_at', 'total_paused_seconds',
        'resolved_at', 'closed_at', 'auto_close_at',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'started_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'auto_close_at' => 'datetime',
        'total_paused_seconds' => 'integer',
    ];

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        TicketVisibilityScope::apply($query, $user);
    }

    public function scopeStatus(Builder $query, TicketStatus|string $status): void
    {
        $query->where('status', $status instanceof TicketStatus ? $status->value : $status);
    }

    public function scopeActiveAssignments(Builder $query): void
    {
        $query->whereIn('status', TicketStatus::activeAssignmentValues());
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function timeLogs(): HasMany
    {
        return $this->hasMany(TicketTimeLog::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(TicketApproval::class);
    }

    public function currentApproval(): HasOne
    {
        return $this->hasOne(TicketApproval::class)->where('is_current', true);
    }

    public function activePauseLog(): HasOne
    {
        return $this->hasOne(TicketTimeLog::class)
            ->whereNull('resumed_at')
            ->latestOfMany('paused_at');
    }

    public function latestHistory(): HasOne
    {
        return $this->hasOne(TicketHistory::class)->latestOfMany('created_at');
    }

    public function isPaused(): bool
    {
        return $this->status->isPaused();
    }
}
