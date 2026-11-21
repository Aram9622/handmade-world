<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_subscriptions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 32);
            $table->primary(['user_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_subscriptions');
    }
};
