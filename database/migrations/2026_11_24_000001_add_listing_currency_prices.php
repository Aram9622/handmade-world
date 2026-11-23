<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedInteger('price')->nullable()->change();
            $table->unsignedInteger('price_amd')->nullable();
            $table->unsignedInteger('price_usd')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('listings')->whereNull('price')->exists()) {
            throw new RuntimeException('Cannot roll back while listings have prices only in AMD or USD.');
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedInteger('price')->change();
            $table->dropColumn(['price_amd', 'price_usd']);
        });
    }
};
