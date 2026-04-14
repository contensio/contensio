<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();          // main, footer-company
            $table->timestamps();
        });

        Schema::create('menu_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('label');                        // Main Navigation

            $table->unique(['menu_id', 'language_id']);
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable();
            $table->string('type', 30);                     // page, post, content_type, taxonomy, custom_url
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('target', 10)->default('_self');  // _self, _blank
            $table->string('icon', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index(['menu_id', 'position']);
            $table->index('parent_id');
        });

        Schema::create('menu_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('label');                        // About Us, Contact
            $table->string('url', 500)->nullable();         // for custom_url type

            $table->unique(['menu_item_id', 'language_id']);
        });

        // Theme registers locations, user assigns menus to them
        Schema::create('menu_locations', function (Blueprint $table) {
            $table->id();
            $table->string('theme', 100);                   // active theme slug
            $table->string('location', 100);                // header, footer_col_1
            $table->foreignId('menu_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['theme', 'location']);
        });

        Schema::create('menu_location_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('label');                        // Header Navigation

            $table->unique(['menu_location_id', 'language_id'], 'mlt_location_language_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_location_translations');
        Schema::dropIfExists('menu_locations');
        Schema::dropIfExists('menu_item_translations');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menu_translations');
        Schema::dropIfExists('menus');
    }
};
