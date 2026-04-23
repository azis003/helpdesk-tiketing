<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCounter extends Model
{
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'date';
    protected $keyType = 'string';

    protected $fillable = ['date', 'last_number'];

    protected $casts = [
        'date' => 'date',
        'last_number' => 'integer',
    ];
}
