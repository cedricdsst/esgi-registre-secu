<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapport_id')->constrained('rapports')->onDelete('cascade');
            $table->string('identification'); // numérique ou alphabétique
            $table->text('libelle');
            $table->string('localisation')->nullable();
            $table->enum('priorite', ['urgent', 'normal', 'faible'])->nullable();
            $table->enum('statut_traitement', ['nouveau', 'en_cours', 'traite', 'reporte'])->default('nouveau');
            $table->boolean('deja_signalee')->default(false);
            $table->date('date_signalement_precedent')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('observations');
    }
};
