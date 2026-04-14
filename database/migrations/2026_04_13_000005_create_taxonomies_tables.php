<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();          // category, tag, genre
            $table->boolean('is_hierarchical')->default(false);
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('taxonomy_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('taxonomy_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('slug', 100);
            $table->json('labels');                         // {singular, plural, create, all, ...}
            $table->string('description', 500)->nullable();

            $table->unique(['taxonomy_id', 'language_id']);
            $table->unique(['slug', 'language_id']);
        });

        // Pivot: which content types use which taxonomies
        Schema::create('content_type_taxonomies', function (Blueprint $table) {
            $table->foreignId('content_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('taxonomy_id')->constrained()->cascadeOnDelete();

            $table->primary(['content_type_id', 'taxonomy_id']);
        });

        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->char('code', 16)->unique();
            $table->foreignId('taxonomy_id')->constrained()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['taxonomy_id', 'parent_id']);
            $table->index('parent_id');
        });

        Schema::create('term_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('description', 500)->nullable();

            $table->unique(['term_id', 'language_id']);
            $table->index(['slug', 'language_id']);
        });

        Schema::create('term_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->foreignId('language_id')->nullable()->constrained('languages')->restrictOnDelete();

            $table->index(['term_id', 'meta_key', 'language_id']);
        });

        // Pivot: content ↔ terms
        Schema::create('content_terms', function (Blueprint $table) {
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->constrained()->cascadeOnDelete();

            $table->primary(['content_id', 'term_id']);
            // Reverse lookup: all content for a term
            $table->index('term_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_terms');
        Schema::dropIfExists('term_meta');
        Schema::dropIfExists('term_translations');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('content_type_taxonomies');
        Schema::dropIfExists('taxonomy_translations');
        Schema::dropIfExists('taxonomies');
    }
};
