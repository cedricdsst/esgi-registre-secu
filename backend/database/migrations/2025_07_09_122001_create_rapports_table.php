<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rapports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_id')->constrained('interventions')->onDelete('cascade');
            $table->foreignId('type_rapport_id')->constrained('types_rapports')->onDelete('cascade');
            $table->date('date_emission');
            $table->enum('statut', ['brouillon', 'finalise', 'signe', 'archive'])->default('brouillon');
            $table->json('equipements_selection')->nullable(); // IDs des équipements de l'API externe
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rapports');
    }
};
