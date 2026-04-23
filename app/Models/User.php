<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['username', 'name', 'email', 'password', 'avatar', 'work_unit_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function workUnit()
    {
        return $this->belongsTo(WorkUnit::class);
    }

    public function reportedTickets()
    {
        return $this->hasMany(Ticket::class, 'reporter_id');
    }

    public function handledTickets()
    {
        return $this->hasMany(Ticket::class, 'handler_id');
    }

    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class);
    }
}
