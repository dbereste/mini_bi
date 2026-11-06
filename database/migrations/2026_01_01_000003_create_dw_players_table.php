<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dw_players', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('player_idt');
            $table->integer('domain');
            $table->string('affiliate_id', 20)->nullable();
            $table->string('btag', 100)->nullable();
            $table->string('country_name', 50)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('platform', 20)->nullable();
            $table->string('status', 20)->nullable();
            $table->integer('is_test')->default(0);
            $table->string('kyc_verified', 10)->nullable();
            $table->dateTime('created')->nullable();
            $table->dateTime('first_deposit_date')->nullable();
            $table->decimal('first_deposit_amount_eur', 20, 2)->nullable();
            $table->dateTime('rec_ins_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dw_players');
    }
};
