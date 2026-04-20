<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('block_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();           // richtext, image, hero
            $table->string('label', 100);                   // Rich Text, Image, Hero
            $table->string('icon', 100)->nullable();        // icon identifier (e.g. heroicon name)
            $table->string('description', 300)->nullable();
            $table->string('category', 30)->default('text'); // text, media, layout, advanced
            $table->boolean('is_core')->default(true);
            $table->string('plugin', 100)->nullable()->index();   // Composer name, e.g. contensio/plugin-faq — NULL for core blocks
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('block_types');
    }
};
