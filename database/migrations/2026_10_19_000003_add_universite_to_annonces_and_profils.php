<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->string('quartier', 120)->change();
            $table->foreignId('universite_id')->nullable()->after('quartier')->constrained('universites')->nullOnDelete();
        });

        Schema::table('profils_etudiants', function (Blueprint $table) {
            $table->foreignId('universite_id')->nullable()->after('etablissement')->constrained('universites')->nullOnDelete();
        });

        Schema::table('publicites_boutiques', function (Blueprint $table) {
            $table->string('quartier_cible', 120)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropConstrainedForeignId('universite_id');
            $table->enum('quartier', [
                'Zogbadje', 'Tankpe', 'Togoudo', 'Bidossessi', 'Arconville', 'Houeto',
                'Ouedo', 'Glo-Djigbe', 'Hevie', 'Kpota', 'Acacia', 'Calavi-Bas',
            ])->change();
        });

        Schema::table('profils_etudiants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('universite_id');
        });

        Schema::table('publicites_boutiques', function (Blueprint $table) {
            $table->string('quartier_cible', 255)->nullable()->change();
        });
    }
};
