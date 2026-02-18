<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_localizations', function (Blueprint $table) {
            $table->json('professional_experience_json')->nullable()->after('summary_text');
            $table->json('clinical_research_json')->nullable()->after('professional_experience_json');
            $table->json('trainings_json')->nullable()->after('clinical_research_json');
        });
    }

    public function down(): void
    {
        Schema::table('cv_localizations', function (Blueprint $table) {
            $table->dropColumn([
                'professional_experience_json',
                'clinical_research_json',
                'trainings_json',
            ]);
        });
    }
};
