<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false);
            $table->timestamp('suspended_at')->nullable();
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable();
            $table->string('moderation_reason', 500)->nullable();
        });

        Schema::table('listing_comments', function (Blueprint $table) {
            $table->timestamp('hidden_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('listing_comments', fn (Blueprint $table) => $table->dropColumn('hidden_at'));
        Schema::table('listings', fn (Blueprint $table) => $table->dropColumn(['hidden_at', 'moderation_reason']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['is_super_admin', 'suspended_at']));
    }
};
