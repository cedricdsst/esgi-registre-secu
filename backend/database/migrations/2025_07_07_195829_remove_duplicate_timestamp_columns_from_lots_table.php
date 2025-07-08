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
        // Vérifier si les colonnes existent avant de les supprimer
        if (Schema::hasColumn('lots', 'createdAt') || Schema::hasColumn('lots', 'modifiedAt')) {
            Schema::table('lots', function (Blueprint $table) {
                if (Schema::hasColumn('lots', 'createdAt')) {
                    $table->dropColumn('createdAt');
                }
                if (Schema::hasColumn('lots', 'modifiedAt')) {
                    $table->dropColumn('modifiedAt');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lots', function (Blueprint $table) {
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('modifiedAt')->useCurrent()->useCurrentOnUpdate();
        });
    }
};
