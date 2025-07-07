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
        Schema::create('partie_lot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partie_id')->constrained('parties')->onDelete('cascade');
            $table->foreignId('lot_id')->constrained('lots')->onDelete('cascade');
            $table->string('libelle')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();

            $table->unique(['partie_id', 'lot_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partie_lot');
    }
};
