<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('niveau_id')->constrained('niveaux')->onDelete('cascade');
            $table->string('nom');
            $table->enum('type', ['privative', 'commune']);
            $table->boolean('isICPE')->default(false);
            $table->boolean('isPrivative')->default(false);
            $table->timestamps();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('modifiedAt')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parties');
    }
};