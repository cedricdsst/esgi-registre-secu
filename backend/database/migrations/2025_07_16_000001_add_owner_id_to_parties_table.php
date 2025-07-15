<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->foreignId('owner_id')->nullable()->after('batiment_id')->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }
}; 