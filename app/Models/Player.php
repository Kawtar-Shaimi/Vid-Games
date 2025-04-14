<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'age',
        'number',
        'nationality'
    ];

    public function matches() {
        return $this->belongsToMany(TournamentMatch::class, 'matches_players', 'player_id');
    }
}
