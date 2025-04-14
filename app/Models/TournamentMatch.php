<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TournamentMatch extends Model
{
    protected $table = 'matches';

    protected $fillable = [
        'date',
        'time',
        'city',
        'tournament_id',
        'host_team_name',
        'guest_team_name',
        'host_team_score',
        'guest_team_score',
    ];

    public function players() {
        return $this->belongsToMany(Player::class, 'matches_players', 'match_id');
    }
}
