<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Une conversation peut desormais etre rattachee a une annonce OU a un avis.
            $table->foreignId('annonce_id')->nullable()->change();
            $table->foreignId('avis_id')->nullable()->after('annonce_id')
                ->constrained('avis')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('conversations')->whereNotNull('avis_id')->delete();

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('avis_id');
            $table->foreignId('annonce_id')->nullable(false)->change();
        });
    }
};
