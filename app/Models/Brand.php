<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'dw_brands';

    public $timestamps = false;

    protected $guarded = [];

    public function players()
    {
        return $this->hasMany(Player::class, 'domain', 'domain');
    }
}
