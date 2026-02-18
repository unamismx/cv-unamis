<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_educations', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('institution_other');
            $table->date('end_date')->nullable()->after('start_date');
            $table->date('completion_date')->nullable()->after('year_completed');
        });
    }

    public function down(): void
    {
        Schema::table('cv_educations', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'completion_date']);
        });
    }
};

