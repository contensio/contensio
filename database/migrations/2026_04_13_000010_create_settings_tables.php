<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50);                   // core, seo, email, media, permalinks
            $table->string('setting_key', 100);
            $table->longText('value')->nullable();
            $table->boolean('is_translatable')->default(false);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['module', 'setting_key']);
            $table->index('module');
        });

        Schema::create('setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->longText('value')->nullable();

            $table->unique(['setting_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_translations');
        Schema::dropIfExists('settings');
    }
};
