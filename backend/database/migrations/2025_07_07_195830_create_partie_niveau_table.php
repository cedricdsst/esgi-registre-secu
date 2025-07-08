<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('partie_niveau', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partie_id')->constrained('parties')->onDelete('cascade');
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('cascade');
            $table->string('libelle')->nullable(); // Libellé spécifique pour ce niveau
            
            // Champs spécifiques à chaque niveau d'une partie (selon consignes)
            $table->integer('effectif_public')->nullable();
            $table->integer('personnel')->nullable();
            $table->decimal('surface_exploitation', 10, 2)->nullable();
            $table->decimal('surface_gla', 10, 2)->nullable();
            $table->decimal('surface_accessible_public', 10, 2)->nullable();
            
            $table->timestamps();
            
            $table->unique(['partie_id', 'niveau_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partie_niveau');
    }
}; 