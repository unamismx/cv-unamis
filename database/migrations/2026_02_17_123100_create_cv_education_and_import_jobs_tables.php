<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_localization_id')->constrained('cv_localizations')->cascadeOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('catalog_institutions')->nullOnDelete();
            $table->string('institution_other', 220)->nullable();
            $table->unsignedSmallInteger('year_completed')->nullable();
            $table->foreignId('degree_id')->nullable()->constrained('catalog_degrees')->nullOnDelete();
            $table->string('degree_other', 220)->nullable();
            $table->string('license_number', 120)->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });

        Schema::create('cv_import_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('original_file_name', 255);
            $table->string('stored_file_path', 255);
            $table->string('detected_locale', 12)->default('unknown');
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->string('parse_status', 40)->default('ready_for_review');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_import_jobs');
        Schema::dropIfExists('cv_educations');
    }
};
