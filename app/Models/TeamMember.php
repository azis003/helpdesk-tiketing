<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['work_unit_id', 'user_id', 'joined_at'];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function workUnit()
    {
        return $this->belongsTo(WorkUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
