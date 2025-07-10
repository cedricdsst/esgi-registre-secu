<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fichiers_rapports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rapport_id')->nullable()->constrained('rapports')->onDelete('cascade');
            $table->foreignId('observation_id')->nullable()->constrained('observations')->onDelete('cascade');
            $table->string('nom_original');
            $table->string('nom_stockage');
            $table->string('chemin_compresse')->nullable();
            $table->integer('version')->default(1);
            $table->integer('taille'); // en bytes
            $table->string('type_mime');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fichiers_rapports');
    }
};
