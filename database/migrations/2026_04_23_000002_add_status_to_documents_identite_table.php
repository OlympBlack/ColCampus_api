<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('documents_identite', function (Blueprint $table) {
            if (!Schema::hasColumn('documents_identite', 'statut_verification')) {
                $table->enum('statut_verification', ['en_attente', 'valide', 'rejete'])->default('en_attente')->after('photo_piece_url');
            }

            if (!Schema::hasColumn('documents_identite', 'commentaire_admin')) {
                $table->text('commentaire_admin')->nullable()->after('statut_verification');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents_identite', function (Blueprint $table) {
            if (Schema::hasColumn('documents_identite', 'commentaire_admin')) {
                $table->dropColumn('commentaire_admin');
            }

            if (Schema::hasColumn('documents_identite', 'statut_verification')) {
                $table->dropColumn('statut_verification');
            }
        });
    }
};
