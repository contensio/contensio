<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contensio_plugin_entries', function (Blueprint $table) {
            $table->id();
            $table->string('plugin', 200)->index()
                  ->comment('Composer package name, e.g. contensio/plugin-faq');
            $table->string('type', 100)->index()
                  ->comment('Entry type defined by the plugin, e.g. group, item, member, file');
            $table->string('title', 500)->nullable();
            $table->string('slug', 500)->nullable()->index();
            $table->longText('content')->nullable();
            $table->json('data')->nullable()
                  ->comment('Plugin-specific fields as JSON');
            $table->string('status', 50)->default('active')->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedBigInteger('parent_id')->nullable()->index()
                  ->comment('Self-reference for nested items, e.g. FAQ items within a group');
            $table->unsignedBigInteger('post_id')->nullable()->index()
                  ->comment('Optional link to a content post');
            $table->unsignedBigInteger('user_id')->nullable()->index()
                  ->comment('Optional link to a user');
            $table->timestamps();

            $table->index(['plugin', 'type']);
            $table->index(['plugin', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contensio_plugin_entries');
    }
};
