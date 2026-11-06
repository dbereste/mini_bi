<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dw_brands', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('domain');
            $table->string('brand_code', 20);
            $table->string('name', 100);
            $table->string('default_currency', 3);
            $table->dateTime('launched_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dw_brands');
    }
};
