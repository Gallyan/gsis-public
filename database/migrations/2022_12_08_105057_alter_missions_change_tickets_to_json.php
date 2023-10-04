<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('tickets');
        });
        Schema::table('missions', function (Blueprint $table) {
            $table->json('tickets')->nullable()->after('to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('tickets');
        });
        Schema::table('missions', function (Blueprint $table) {
            $table->boolean('tickets')->default(0)->after('to');
        });
    }
};
