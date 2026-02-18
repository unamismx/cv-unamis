<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cvs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('draft');
            $table->text('institutional_address');
            $table->timestamp('last_published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cv_localizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cv_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 2);
            $table->string('title_name', 220)->nullable();
            $table->string('office_phone', 50)->nullable();
            $table->string('fax_number', 50)->nullable();
            $table->string('email', 180)->nullable();
            $table->string('position_label', 180)->nullable();
            $table->text('summary_text')->nullable();
            $table->timestamps();

            $table->unique(['cv_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_localizations');
        Schema::dropIfExists('cvs');
    }
};
