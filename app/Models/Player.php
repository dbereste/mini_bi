<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
    protected $table = 'dw_players';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'is_test' => 'boolean',
        'created' => 'datetime',
        'first_deposit_date' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'domain', 'domain');
    }

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class, 'affiliate_id', 'affiliate_id');
    }

    public function deposits()
    {
        return $this->hasMany(DepositTransaction::class, 'player_idt', 'player_idt');
    }

    public function withdrawals()
    {
        return $this->hasMany(WithdrawTransaction::class, 'player_idt', 'player_idt');
    }

    public function dailyActivity()
    {
        return $this->hasMany(PlayerDailyActivity::class, 'player_idt', 'player_idt');
    }
}
