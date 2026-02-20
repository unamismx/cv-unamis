<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS cv_document_seals_year_sequence_unique');
        }

        Schema::table('cv_document_seals', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropUnique(['year', 'sequence']);
            }
            $table->unique(['year', 'locale', 'sequence'], 'cv_document_seals_year_locale_sequence_unique');
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS cv_document_seals_year_locale_sequence_unique');
        }

        Schema::table('cv_document_seals', function (Blueprint $table) {
            if (Schema::getConnection()->getDriverName() !== 'sqlite') {
                $table->dropUnique('cv_document_seals_year_locale_sequence_unique');
            }
            $table->unique(['year', 'sequence']);
        });
    }
};
