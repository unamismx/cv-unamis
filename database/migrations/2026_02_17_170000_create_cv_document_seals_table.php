<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_document_seals', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 40)->unique();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('sequence');
            $table->foreignId('cv_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 2);
            $table->char('hash_sha256', 64);
            $table->char('signature_hmac', 64);
            $table->timestamp('signed_at');
            $table->string('signer_email', 180);
            $table->timestamps();

            $table->unique(['year', 'locale', 'sequence']);
            $table->index(['cv_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_document_seals');
    }
};
