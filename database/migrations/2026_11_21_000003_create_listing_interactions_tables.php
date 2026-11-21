<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_likes', function (Blueprint $table) {
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->index('user_id');
            $table->primary(['listing_id', 'user_id']);
        });

        Schema::create('listing_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->index('user_id');
            $table->text('body');
            $table->timestamps();
            $table->index(['listing_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_comments');
        Schema::dropIfExists('listing_likes');
    }
};
