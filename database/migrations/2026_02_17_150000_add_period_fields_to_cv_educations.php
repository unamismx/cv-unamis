<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_educations', function (Blueprint $table) {
            $table->unsignedSmallInteger('start_year')->nullable()->after('institution_other');
            $table->unsignedSmallInteger('end_year')->nullable()->after('start_year');
            $table->boolean('is_ongoing')->default(false)->after('end_year');
        });
    }

    public function down(): void
    {
        Schema::table('cv_educations', function (Blueprint $table) {
            $table->dropColumn(['start_year', 'end_year', 'is_ongoing']);
        });
    }
};
