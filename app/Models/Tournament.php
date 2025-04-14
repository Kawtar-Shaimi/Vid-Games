<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    
    protected $fillable = [
        'title',
        'type',
        'start_date',
        'end_date',
        'location',
        'prize',
    ];


}
