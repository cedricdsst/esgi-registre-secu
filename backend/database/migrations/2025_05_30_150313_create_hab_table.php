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
        Schema::create('habs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('batiment_id')->constrained('batiments')->onDelete('cascade');
            $table->foreignId('hab_famille_id')->constrained('hab_familles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habs');
    }
};
