<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_degrees', function (Blueprint $table) {
            $table->string('external_source', 40)->nullable()->after('name_en');
            $table->string('external_id', 120)->nullable()->after('external_source');

            $table->index(['external_source', 'external_id'], 'catalog_degrees_source_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_degrees', function (Blueprint $table) {
            $table->dropIndex('catalog_degrees_source_id_idx');
            $table->dropColumn(['external_source', 'external_id']);
        });
    }
};
