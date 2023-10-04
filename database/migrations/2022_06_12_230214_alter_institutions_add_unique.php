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
        Schema::table('institutions', function (Blueprint $table) {
            $table->unique(['name', 'contract', 'allocation']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropUnique('institutions_name_contract_allocation_unique');
        });
    }
};
