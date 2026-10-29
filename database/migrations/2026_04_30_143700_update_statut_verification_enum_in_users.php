<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasColumn('users', 'statut_verification')) {
            DB::statement("ALTER TABLE users MODIFY statut_verification ENUM('quarantaine', 'pending_validation', 'non_rempli', 'en_attente', 'valide', 'rejete') NOT NULL DEFAULT 'quarantaine'");
            DB::statement("UPDATE users SET statut_verification = 'quarantaine' WHERE statut_verification = 'non_rempli'");
            DB::statement("UPDATE users SET statut_verification = 'pending_validation' WHERE statut_verification = 'en_attente'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasColumn('users', 'statut_verification')) {
            DB::statement("UPDATE users SET statut_verification = 'non_rempli' WHERE statut_verification = 'quarantaine'");
            DB::statement("UPDATE users SET statut_verification = 'en_attente' WHERE statut_verification = 'pending_validation'");
            DB::statement("ALTER TABLE users MODIFY statut_verification ENUM('non_rempli','en_attente','valide','rejete') NOT NULL DEFAULT 'non_rempli'");
        }
    }
};
