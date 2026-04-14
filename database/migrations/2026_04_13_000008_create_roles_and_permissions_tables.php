<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();           // super_admin, admin, editor
            $table->boolean('is_system')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('role_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->json('labels');                         // {title: "Super Admin", description: "..."}

            $table->unique(['role_id', 'language_id']);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module', 50);                   // content, media, taxonomy, menu, seo, users, system
            $table->string('name', 100)->unique();           // content.create, media.upload, system.plugins

            $table->index('module');
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('content_type_id')->nullable()->constrained()->cascadeOnDelete();

            $table->primary(['role_id', 'permission_id', 'content_type_id'], 'rp_primary');
            // Lookup: all permissions for a role
            $table->index('role_id');
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->restrictOnDelete();

            $table->primary(['user_id', 'role_id']);
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('role_translations');
        Schema::dropIfExists('roles');
    }
};
