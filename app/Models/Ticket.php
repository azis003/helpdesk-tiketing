<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    public function category()
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function priority()
    {
        return $this->belongsTo(TicketPriority::class, 'priority_id');
    }

    public function timeLogs()
    {
        return $this->hasMany(TicketTimeLog::class);
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function histories()
    {
        return $this->hasMany(TicketHistory::class);
    }

    public function approvals()
    {
        return $this->hasMany(TicketApproval::class);
    }
}
