<?php

namespace App\Models;

use App\Enums\PauseReason;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketTimeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'pause_reason', 'note', 'paused_at', 'resumed_at', 'duration_seconds'
    ];

    protected $casts = [
        'pause_reason' => PauseReason::class,
        'paused_at' => 'datetime',
        'resumed_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function scopeActivePause(Builder $query): void
    {
        $query->whereNull('resumed_at');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
