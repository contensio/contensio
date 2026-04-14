<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            // Stores all blocks as a JSON array.
            // Structure: [{id, type, is_active, data, settings, translations}]
            // Non-translatable field values live in `data`.
            // Translatable field values live in `translations[language_id]`.
            $table->json('blocks')->nullable()->after('featured_image_id');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('blocks');
        });
    }
};
