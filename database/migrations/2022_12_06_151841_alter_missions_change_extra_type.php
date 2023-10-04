<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('extra');
        });
        Schema::table('missions', function (Blueprint $table) {
            $table->json('extra')->nullable()->after('hotels');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('extra');
        });
        Schema::table('missions', function (Blueprint $table) {
            $table->boolean('extra')->default(0)->after('hotels');
        });
    }
};
