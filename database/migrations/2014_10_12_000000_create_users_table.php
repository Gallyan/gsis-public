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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('lastname');
            $table->string('firstname');
            $table->date('birthday')->nullable();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('employer')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('hom_adr')->nullable();
            $table->string('hom_zip')->nullable();
            $table->string('hom_cit')->nullable();
            $table->string('pro_ins')->nullable();
            $table->string('pro_adr')->nullable();
            $table->string('pro_zip')->nullable();
            $table->string('pro_cit')->nullable();
            $table->string('locale', 2)->default('fr');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement(
            'ALTER TABLE users ADD FULLTEXT fulltext_index(lastname, firstname, email)'
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
