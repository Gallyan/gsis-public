<?php

use App\Models\Purchase;
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
        foreach (Purchase::whereNotNull('wp')->get() as $p) {
            $p->wp = substr($p->wp, 2);
            $p->save();
        }

        Schema::table('purchases', function (Blueprint $table) {
            $table->integer('wp')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('wp')->nullable()->change();
        });

        foreach (Purchase::whereNotNull('wp')->get() as $p) {
            $p->wp = 'wp'.$p->wp;
            $p->save();
        }
    }
};
