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
        Schema::create('droits_batiment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('batiment_id')->constrained('batiments')->onDelete('cascade');
            $table->boolean('lecture')->default(false);
            $table->boolean('ecriture')->default(false);
            $table->timestamps();
            
            $table->unique(['utilisateur_id', 'batiment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('droits_batiment');
    }
};
