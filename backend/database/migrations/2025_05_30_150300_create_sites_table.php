<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('adresse');
            $table->string('code_postal');
            $table->string('ville');
            $table->string('pays')->default('France');
            $table->text('description')->nullable();
            $table->string('client_id')->nullable(); // Pour lier le site à un client spécifique
            $table->timestamps();
            $table->softDeletes(); // Pour garder l'historique des sites supprimés
        });
    }

    public function down()
    {
        Schema::dropIfExists('sites');
    }
}; 