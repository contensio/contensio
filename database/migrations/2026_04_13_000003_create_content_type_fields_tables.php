<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Custom fields schema.
 *
 * Two-level hierarchy: field groups contain fields. "Sections" are a simple
 * string attribute on a field (for visual grouping in the edit UI), not a
 * separate entity.
 *
 * Groups attach to content types (and, in a future version, potentially to
 * taxonomies / users / media) through a polymorphic pivot:
 *     field_group_attachments(field_group_id, attachable_type, attachable_id)
 *
 * v1 only writes 'content_type' into attachable_type — the polymorphic shape
 * is there so v1.x can add more attachment targets without a migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Field groups ────────────────────────────────────────────────
        Schema::create('field_groups', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();        // machine key: 'device-specs'
            $table->string('label', 200);                // fallback label (English), used when no translation
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('field_group_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('label', 200);
            $table->text('description')->nullable();

            $table->unique(['field_group_id', 'language_id'], 'fgt_group_language_unique');
        });

        // ── Polymorphic attachment pivot ────────────────────────────────
        Schema::create('field_group_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_group_id')->constrained()->cascadeOnDelete();
            $table->string('attachable_type', 100);       // 'content_type' (v1)
            $table->unsignedBigInteger('attachable_id');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id']);
            $table->unique(['field_group_id', 'attachable_type', 'attachable_id'], 'fga_unique');
        });

        // ── Fields ──────────────────────────────────────────────────────
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_group_id')->constrained()->cascadeOnDelete();
            $table->string('key', 100);                   // machine key: 'resolution'
            $table->string('type', 30);                   // text, textarea, rich-text, number, boolean,
                                                          // date, select, multi-select, media, url
            $table->string('section', 100)->nullable();   // optional visual section header
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_translatable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->json('config')->nullable();           // type-specific: options, min/max, mime filter
            $table->json('visibility_rules')->nullable(); // reserved for conditional display (future)
            $table->timestamps();

            $table->unique(['field_group_id', 'key']);
            $table->index(['field_group_id', 'position']);
        });

        Schema::create('field_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('label', 200);
            $table->string('placeholder', 200)->nullable();
            $table->text('help_text')->nullable();

            $table->unique(['field_id', 'language_id'], 'ft_field_language_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('field_translations');
        Schema::dropIfExists('fields');
        Schema::dropIfExists('field_group_attachments');
        Schema::dropIfExists('field_group_translations');
        Schema::dropIfExists('field_groups');
    }
};
