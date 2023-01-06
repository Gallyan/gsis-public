<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Mission;

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
            $table->string('others')->nullable()->after('hotels');
            $table->boolean('registration')->default(0)->after('hotels');
            $table->boolean('parking')->default(0)->after('hotels');
            $table->boolean('rental_car')->default(0)->after('hotels');
            $table->boolean('personal_car')->default(0)->after('hotels');
            $table->boolean('transport')->default(0)->after('hotels');
            $table->boolean('taxi')->default(0)->after('hotels');
            $table->string('meal')->nullable()->after('hotels');

            $table->dropColumn(['extra','from','to']);
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->string('to')->default('home')->after('return');
            $table->string('from')->default('home')->after('return');
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
            $table->json('extra')->nullable()->after('hotels');
        });

        Mission::query()->update( [ 'extra' => '[]' ] );

        Schema::table('missions', function (Blueprint $table) {
            $table->dropColumn(['meal','taxi','transport','personal_car','rental_car','parking','registration','others','from','to']);
        });

        Schema::table('missions', function (Blueprint $table) {
            $table->boolean('to')->default(1)->after('return');
            $table->boolean('from')->default(1)->after('return');
        });

    }
};
