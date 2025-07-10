<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventaires_partie', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partie_id')->constrained('parties')->onDelete('cascade');
            $table->integer('product_id'); // ID du produit depuis l'API externe
            $table->string('localisation');
            $table->json('donnees_produit')->nullable(); // Cache des données produit de l'API
            $table->integer('quantite')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventaires_partie');
    }
};
