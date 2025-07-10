<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('types_rapports', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->string('sous_titre')->nullable();
            $table->enum('periodicite', ['annuelle', 'semestrielle', 'triennale', 'quinquennale', 'biannuelle', 'ponctuelle']);
            $table->enum('typologie_batiment', ['ERP', 'IGH', 'HAB', 'BUP'])->nullable();
            $table->boolean('organisme_agree_requis')->default(false);
            $table->timestamp('next_check_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('types_rapports');
    }
};
