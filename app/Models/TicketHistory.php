<?php

namespace App\Models;

use App\Enums\HistoryAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketHistory extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'ticket_id', 'actor_id', 'new_handler_id', 'time_log_id',
        'from_status', 'to_status', 'action', 'note', 'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        // 'action' => HistoryAction::class, // Action can be complex, sometimes string
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function newHandler()
    {
        return $this->belongsTo(User::class, 'new_handler_id');
    }

    public function timeLog()
    {
        return $this->belongsTo(TicketTimeLog::class);
    }
}
