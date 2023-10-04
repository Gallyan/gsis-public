<?php

use App\Models\Mission;
use App\Models\User;
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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Mission::class)->constrained();
            $table->foreignIdFor(User::class)->constrained();
            $table->json('actual_costs_meals')->nullable();
            $table->integer('flat_rate_lunch')->nullable();
            $table->integer('flat_rate_dinner')->nullable();
            $table->json('transports')->nullable();
            $table->json('hotels')->nullable();
            $table->json('registrations')->nullable();
            $table->json('miscs')->nullable();
            $table->text('comments')->nullable();
            $table->string('status')->default('on-hold');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
