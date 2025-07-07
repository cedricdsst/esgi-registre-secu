<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('droits_niveau', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('cascade');
            $table->boolean('lecture')->default(false);
            $table->boolean('ecriture')->default(false);
            $table->timestamps();

            $table->unique(['utilisateur_id', 'niveau_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('droits_niveau');
    }
}; 