<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_gcp_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_localization_id')->constrained('cv_localizations')->cascadeOnDelete();
            $table->string('provider', 180)->nullable();
            $table->string('course_name', 220)->nullable();
            $table->string('guideline_version', 60)->nullable();
            $table->string('certificate_language', 40)->nullable();
            $table->string('participant_name', 220)->nullable();
            $table->string('certificate_id', 120)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status', 30)->default('unknown');
            $table->string('verification_url', 255)->nullable();
            $table->string('certificate_file_path', 255)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_gcp_certifications');
    }
};

