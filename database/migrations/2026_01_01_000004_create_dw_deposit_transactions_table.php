<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dw_deposit_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('transaction_idt');
            $table->unsignedBigInteger('player_idt');
            $table->integer('domain');
            $table->string('pay_method_name', 50)->nullable();
            $table->string('pay_system_name', 30)->nullable();
            $table->string('platform', 20)->nullable();
            $table->string('status', 20)->nullable();
            $table->decimal('amount', 20, 2)->nullable();
            $table->decimal('amount_eur', 20, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->dateTime('created')->nullable();
            $table->dateTime('rec_ins_date')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dw_deposit_transactions');
    }
};
