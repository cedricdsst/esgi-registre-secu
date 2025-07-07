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
        Schema::create('ighs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('batiment_id')->constrained('batiments')->onDelete('cascade');
            $table->foreignId('igh_classe_id')->constrained('igh_classes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ighs');
    }
};
