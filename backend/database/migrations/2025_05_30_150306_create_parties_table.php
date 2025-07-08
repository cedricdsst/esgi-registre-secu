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
            $table->foreignId('batiment_id')->constrained('batiments')->onDelete('cascade');
            $table->string('nom');
            $table->enum('type', ['privative', 'commune']);
            $table->boolean('isICPE')->default(false);
            $table->boolean('isPrivative')->default(false);
            $table->text('activites_erp')->nullable(); // Pour les activités ERP
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