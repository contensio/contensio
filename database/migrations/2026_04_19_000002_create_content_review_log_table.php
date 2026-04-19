<?php

/**
 * Contensio - The open content platform for Laravel.
 * Content approval workflow — immutable audit trail of every review action.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_review_log', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('user_id')->nullable(); // null when user deleted

            // submitted | approved | soft_rejected | hard_rejected
            $table->string('action', 30);

            // Reviewer's notes (for rejections) or null.
            $table->text('notes')->nullable();

            // Append-only — no updated_at.
            $table->timestamp('created_at');

            $table->foreign('content_id')->references('id')->on('contents')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['content_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_review_log');
    }
};
