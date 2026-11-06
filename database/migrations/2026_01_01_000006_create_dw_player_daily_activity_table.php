<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dw_player_daily_activity', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('player_idt');
            $table->integer('domain');
            $table->date('reporting_date');
            $table->decimal('deposit_amount_eur', 20, 2)->nullable();
            $table->integer('deposit_count')->nullable();
            $table->decimal('withdraw_amount_eur', 20, 2)->nullable();
            $table->integer('withdraw_count')->nullable();
            $table->decimal('ggr_casino_eur', 20, 2)->nullable();
            $table->decimal('ggr_sport_eur', 20, 2)->nullable();
            $table->decimal('bonus_eur', 20, 2)->nullable();
            $table->decimal('ngr', 20, 2)->nullable();
            $table->dateTime('rec_ins_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dw_player_daily_activity');
    }
};
