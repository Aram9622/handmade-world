<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->text('description_html')->nullable();
            $table->json('characteristics')->nullable();
            $table->json('gallery')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('listings', fn (Blueprint $table) => $table->dropColumn(['description_html', 'characteristics', 'gallery']));
    }
};
