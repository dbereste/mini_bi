<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dw_affiliates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('affiliate_id', 20);
            $table->integer('domain')->nullable();
            $table->string('name', 255);
            $table->string('affiliate_type', 30)->nullable();
            $table->string('account_manager', 100)->nullable();
            $table->string('status', 20)->nullable();
            $table->dateTime('created')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dw_affiliates');
    }
};
