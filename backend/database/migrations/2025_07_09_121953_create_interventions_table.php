<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->string('intitule');
            $table->string('entreprise_nom');
            $table->string('intervenant_nom');
            $table->foreignId('type_intervention_id')->constrained('types_interventions')->onDelete('cascade');
            $table->enum('statut', ['planifie', 'en_cours', 'termine', 'annule'])->default('planifie');
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('interventions');
    }
};
