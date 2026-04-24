<?php

namespace App\Models;

use App\Enums\HistoryAction;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'ticket_id', 'actor_id', 'new_handler_id', 'time_log_id',
        'from_status', 'to_status', 'action', 'note', 'created_at'
    ];

    protected $casts = [
        'from_status' => TicketStatus::class,
        'to_status' => TicketStatus::class,
        'action' => HistoryAction::class,
        'created_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function newHandler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_handler_id');
    }

    public function timeLog(): BelongsTo
    {
        return $this->belongsTo(TicketTimeLog::class);
    }
}
