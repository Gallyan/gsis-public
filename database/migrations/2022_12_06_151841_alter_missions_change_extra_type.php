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
    public function up()
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
     *
     * @return void
     */
    public function down()
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn('extra');
        });
        Schema::table('missions', function (Blueprint $table) {
            $table->boolean('extra')->default(0)->after('hotels');
        });
    }
};
