<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('niveaux', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batiment_id')->constrained('batiments')->onDelete('cascade');
            $table->string('nom');
            $table->integer('numero_etage');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes(); // Pour garder l'historique des niveaux supprimés
        });
    }

    public function down()
    {
        Schema::dropIfExists('niveaux');
    }
}; 