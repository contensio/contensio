<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->char('code', 16)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('file_name');                    // original-photo.jpg
            $table->string('file_path', 500);               // uploads/2026/04/original-photo.jpg
            $table->string('disk', 20)->default('public');   // local, s3
            $table->string('mime_type', 100);                // image/jpeg, application/pdf
            $table->unsignedBigInteger('file_size');         // bytes
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->string('folder', 200)->nullable();       // admin folder organization
            $table->timestamps();

            $table->index('user_id');
            $table->index('mime_type');
            $table->index('folder');
        });

        Schema::create('media_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('alt_text', 500)->nullable();
            $table->string('title')->nullable();
            $table->text('caption')->nullable();

            $table->unique(['media_id', 'language_id']);
        });

        Schema::create('media_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->foreignId('language_id')->nullable()->constrained('languages')->restrictOnDelete();

            $table->index(['media_id', 'meta_key', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_meta');
        Schema::dropIfExists('media_translations');
        Schema::dropIfExists('media');
    }
};
