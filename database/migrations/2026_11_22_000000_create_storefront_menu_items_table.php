<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_menu_items', function (Blueprint $table) {
            $table->string('key', 32)->primary();
            $table->json('labels');
            $table->unsignedSmallInteger('position');
            $table->boolean('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_menu_items');
    }
};
