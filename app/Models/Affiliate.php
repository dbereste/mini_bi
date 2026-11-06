<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Affiliate extends Model
{
    protected $table = 'dw_affiliates';

    public $timestamps = false;

    protected $guarded = [];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'domain', 'domain');
    }

    public function players()
    {
        return $this->hasMany(Player::class, 'affiliate_id', 'affiliate_id');
    }
}
