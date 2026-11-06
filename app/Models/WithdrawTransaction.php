<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawTransaction extends Model
{
    protected $table = 'dw_withdraw_transactions';

    public $timestamps = false;

    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_idt', 'player_idt');
    }
}
