<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('batiments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->onDelete('cascade');
            $table->string('name');
            $table->string('type'); // ERP, IGH, HAB, BUP, ICPE
            $table->boolean('isICPE')->default(false);
            $table->timestamps();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('modifiedAt')->useCurrent()->useCurrentOnUpdate();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('batiments');
    }
};