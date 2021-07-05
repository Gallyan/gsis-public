<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->string('subject'); // Objet de l'achat hors contrat
            $table->bigInteger('institution_id')->unsigned(); // Institutions et contrat de rattachement
            $table->string('supplier')->nullable(); // Fournisseurs
            $table->text('books')->nullable(); // Ouvrages, Json (Titre Auteur ISBN)
            $table->text('comments')->nullable(); // Commentaires
            $table->string('status')->default('on-hold');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('institution_id')->references('id')->on('institutions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
