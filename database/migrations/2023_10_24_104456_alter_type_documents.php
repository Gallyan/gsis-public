<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('documents')
              ->where('type', 'car')
              ->update(['type' => 'car-registration']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('documents')
              ->where('type', 'car-registration')
              ->update(['type' => 'car']);
    }
};
