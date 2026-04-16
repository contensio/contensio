<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->id();
            $table->char('code', 16)->unique();
            $table->foreignId('content_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('draft'); // draft, published, scheduled, trashed
            $table->foreignId('featured_image_id')->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->boolean('allow_comments')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Main query: list published content of a type, ordered by date
            $table->index(['content_type_id', 'status', 'published_at']);
            // Author's content
            $table->index(['author_id', 'content_type_id', 'status']);
            // Parent lookup for hierarchical types
            $table->index('parent_id');
        });

        Schema::create('content_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('title', 500);
            $table->string('slug', 500);
            $table->longText('body')->nullable();
            $table->text('excerpt')->nullable();
            // SEO
            $table->string('meta_title', 300)->nullable();
            $table->string('meta_description', 500)->nullable();
            $table->string('og_image', 500)->nullable();

            $table->unique(['content_id', 'language_id']);
            // URL resolution: find content by slug in a language
            $table->index(['slug', 'language_id']);
        });

        Schema::create('content_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('fields')->cascadeOnDelete();
            $table->foreignId('language_id')->nullable()->constrained('languages')->restrictOnDelete();
            $table->longText('value')->nullable();
            $table->unsignedSmallInteger('position')->default(0); // for repeater fields

            $table->index(['content_id', 'field_id', 'language_id']);
        });

        Schema::create('content_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->foreignId('language_id')->nullable()->constrained('languages')->restrictOnDelete();

            $table->index(['content_id', 'meta_key', 'language_id']);
        });

        Schema::create('autosaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->json('data');
            $table->timestamp('created_at');

            $table->unique(['content_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autosaves');
        Schema::dropIfExists('content_meta');
        Schema::dropIfExists('content_field_values');
        Schema::dropIfExists('content_translations');
        Schema::dropIfExists('contents');
    }
};
