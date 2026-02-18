<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('catalog_institutions', function (Blueprint $table) {
            $table->string('external_source', 40)->nullable()->after('country_name');
            $table->string('external_id', 120)->nullable()->after('external_source');
            $table->string('municipality_name', 120)->nullable()->after('state_name');
            $table->string('city_name', 120)->nullable()->after('municipality_name');

            $table->index(['external_source', 'external_id'], 'catalog_inst_source_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('catalog_institutions', function (Blueprint $table) {
            $table->dropIndex('catalog_inst_source_id_idx');
            $table->dropColumn([
                'external_source',
                'external_id',
                'municipality_name',
                'city_name',
            ]);
        });
    }
};
