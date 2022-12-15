<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            $table->dropColumn('accomodation');
        });
        DB::table('missions')->whereNull('hotels')->update(['hotels'=>'[]']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('missions', function (Blueprint $table) {
            $table->boolean('accomodation')->default(0)->after('tickets');
        });
        DB::table('missions')->where('hotels','[]')->update(['hotels'=>null]);
    }
};
