<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cv_support_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 40);
            $table->string('title', 180);
            $table->string('original_name', 255);
            $table->string('file_path', 255);
            $table->unsignedInteger('file_size_bytes');
            $table->string('mime_type', 120)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index(['category', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_support_documents');
    }
};

