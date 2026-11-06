<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerDailyActivity extends Model
{
    protected $table = 'dw_player_daily_activity';

    public $timestamps = false;

    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_idt', 'player_idt');
    }
}
