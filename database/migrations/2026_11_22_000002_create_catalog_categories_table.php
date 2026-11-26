<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_categories', function (Blueprint $table) {
            $table->string('slug', 23)->primary();
            $table->json('names');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_categories');
    }
};
