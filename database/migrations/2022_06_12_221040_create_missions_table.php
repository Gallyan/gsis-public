<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Institution;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('subject');
            $table->foreignIdFor(Institution::class)->constrained();
            $table->string('wp')->nullable();
            $table->boolean('conference')->default(0);
            $table->boolean('costs')->default(0);
            $table->string('dest_country')->nullable();
            $table->string('dest_city')->nullable();
            $table->date('departure');
            $table->date('return');
            $table->boolean('from')->default(1);
            $table->boolean('to')->default(1);
            $table->boolean('tickets')->default(0);
            $table->boolean('accomodation')->default(0);
            $table->json('hotels')->nullable();
            $table->boolean('extra')->default(0);
            $table->text('comments')->nullable();
            $table->string('status')->default('on-hold');
            $table->string('om')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('missions');
    }
};
