<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_settings', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->boolean('use_default_price')->default(false);
            $table->unsignedInteger('default_price')->nullable();
        });

        DB::table('marketplace_settings')->insert(['id' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_settings');
    }
};
