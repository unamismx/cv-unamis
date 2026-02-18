<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_document_seals', function (Blueprint $table) {
            $table->dropUnique(['year', 'sequence']);
            $table->unique(['year', 'locale', 'sequence'], 'cv_document_seals_year_locale_sequence_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cv_document_seals', function (Blueprint $table) {
            $table->dropUnique('cv_document_seals_year_locale_sequence_unique');
            $table->unique(['year', 'sequence']);
        });
    }
};
