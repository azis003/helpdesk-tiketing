<?php

namespace App\Models;

use App\Enums\CommentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketComment extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = ['ticket_id', 'user_id', 'body', 'type', 'created_at'];

    protected $casts = [
        'type' => CommentType::class,
        'created_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class, 'comment_id');
    }
}
