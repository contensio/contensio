<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();          // page, post, device
            $table->string('icon', 50)->nullable();         // icon class
            $table->boolean('has_slug')->default(true);
            $table->boolean('has_editor')->default(true);
            $table->boolean('has_excerpt')->default(false);
            $table->boolean('has_featured_image')->default(false);
            $table->boolean('has_categories')->default(false);
            $table->boolean('has_tags')->default(false);
            $table->boolean('has_comments')->default(false);
            $table->boolean('has_seo')->default(true);
            $table->boolean('has_autosave')->default(true);
            $table->boolean('is_hierarchical')->default(false); // pages have parent/child
            $table->boolean('is_system')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('content_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('slug', 100);                   // devices, appareils
            $table->json('labels');                         // {singular, plural, create, edit, ...}
            $table->string('description', 500)->nullable();

            $table->unique(['content_type_id', 'language_id']);
            $table->unique(['slug', 'language_id']);
        });

        Schema::create('content_type_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->constrained()->cascadeOnDelete();
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->foreignId('language_id')->nullable()->constrained('languages')->restrictOnDelete();

            $table->index(['content_type_id', 'meta_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_type_meta');
        Schema::dropIfExists('content_type_translations');
        Schema::dropIfExists('content_types');
    }
};
