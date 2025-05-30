<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nom')->after('name');
            $table->string('prenom')->after('nom');
            $table->string('role')->default('user')->after('prenom');
            $table->string('organisation')->nullable()->after('role');
            $table->dropColumn('name'); // Supprimer l'ancien champ name
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->dropColumn(['nom', 'prenom', 'role', 'organisation']);
        });
    }
};