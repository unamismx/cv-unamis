<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_institutions', function (Blueprint $table) {
            $table->id();
            $table->string('institution_type', 40);
            $table->string('name', 220);
            $table->string('state_name', 120)->nullable();
            $table->string('country_name', 120)->default('México');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('catalog_degrees', function (Blueprint $table) {
            $table->id();
            $table->string('degree_type', 60);
            $table->string('name_es', 220);
            $table->string('name_en', 220)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_degrees');
        Schema::dropIfExists('catalog_institutions');
    }
};
