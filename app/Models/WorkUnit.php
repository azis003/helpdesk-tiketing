<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkUnit extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'is_active'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }
}
