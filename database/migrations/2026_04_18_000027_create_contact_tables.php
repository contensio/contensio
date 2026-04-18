<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Fields (default + extra) ───────────────────────────────────────────
        Schema::create('contact_fields', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);                  // text|textarea|select|multiselect|phone|date|url|email|rating|checkbox|file
            $table->string('key', 80)->unique();          // machine name (name, email, subject, message, ...)
            $table->boolean('is_default')->default(false);// default fields cannot be deleted
            $table->boolean('required')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('options')->nullable();          // select/multiselect choices, min/max for text, etc.
            $table->json('conditional')->nullable();      // {field_key, operator, value} — show/hide rule
            $table->timestamps();

            $table->index('sort_order');
        });

        Schema::create('contact_field_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_field_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('label', 200);
            $table->string('placeholder', 200)->nullable();
            $table->string('help_text', 500)->nullable();

            $table->unique(['contact_field_id', 'language_id']);
        });

        // ── Messages (inbox) ──────────────────────────────────────────────────
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('new'); // new|read|replied|spam
            $table->string('name', 200);
            $table->string('email', 200);
            $table->string('subject', 500)->nullable();
            $table->text('message');
            $table->json('extra_data')->nullable();       // extra field values keyed by field key
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('email');
        });

        Schema::create('contact_message_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_message_id')->constrained()->cascadeOnDelete();
            $table->string('disk', 50)->default('public');
            $table->string('file_path');
            $table->string('file_name', 255);
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index('contact_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_message_files');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('contact_field_translations');
        Schema::dropIfExists('contact_fields');
    }
};
