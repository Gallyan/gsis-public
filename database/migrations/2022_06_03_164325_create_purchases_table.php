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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('subject');
            $table->foreignIdFor(Institution::class)->constrained();
            $table->string('wp');
            $table->json('miscs')->nullable(); // Achats divers, Json (Objet Fournisseur Date Montant Devise)
            $table->text('comments')->nullable(); // Commentaires
            $table->string('status')->default('on-hold');
            $table->float('amount')->nullable();
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
        Schema::dropIfExists('purchases');
    }
};
