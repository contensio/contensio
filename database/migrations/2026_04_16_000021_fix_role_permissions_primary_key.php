<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * MySQL does not allow NULL in composite primary key columns.
 * The original migration declared content_type_id as nullable but included
 * it in the PK, which MySQL silently coerced to NOT NULL.
 *
 * Fix: drop the composite PK, add a surrogate auto-increment id as PK,
 * keep content_type_id nullable (NULL = global, no content-type scope).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            // Drop the broken composite primary key
            $table->dropPrimary('rp_primary');

            // Add surrogate PK
            $table->id()->first();

            // content_type_id can now actually be NULL (global permission)
            $table->unsignedBigInteger('content_type_id')->nullable()->change();

            // Retain a useful lookup index
            $table->index(['role_id', 'permission_id'], 'rp_role_permission');
        });
    }

    public function down(): void
    {
        Schema::table('role_permissions', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->dropIndex('rp_role_permission');
            $table->foreignId('content_type_id')->nullable()->constrained()->cascadeOnDelete()->change();
            $table->primary(['role_id', 'permission_id', 'content_type_id'], 'rp_primary');
        });
    }
};
