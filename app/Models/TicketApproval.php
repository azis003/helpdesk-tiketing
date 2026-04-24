<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'requested_by', 'reviewed_by', 'status', 'is_current', 'note', 'reviewed_at'
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
        'is_current' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    public function scopeCurrent(Builder $query): void
    {
        $query->where('is_current', true);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
