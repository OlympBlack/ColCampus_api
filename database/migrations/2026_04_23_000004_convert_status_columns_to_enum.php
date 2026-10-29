<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasColumn('users', 'statut_verification')) {
            DB::statement("ALTER TABLE users MODIFY statut_verification ENUM('non_rempli','en_attente','valide','rejete') NOT NULL DEFAULT 'non_rempli'");
        }

        if (Schema::hasColumn('documents_identite', 'statut_verification')) {
            DB::statement("ALTER TABLE documents_identite MODIFY statut_verification ENUM('en_attente','valide','rejete') NOT NULL DEFAULT 'en_attente'");
        }
    }

    public function down(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (Schema::hasColumn('users', 'statut_verification')) {
            DB::statement("ALTER TABLE users MODIFY statut_verification VARCHAR(255) NOT NULL DEFAULT 'non_rempli'");
        }

        if (Schema::hasColumn('documents_identite', 'statut_verification')) {
            DB::statement("ALTER TABLE documents_identite MODIFY statut_verification VARCHAR(255) NOT NULL DEFAULT 'en_attente'");
        }
    }
};