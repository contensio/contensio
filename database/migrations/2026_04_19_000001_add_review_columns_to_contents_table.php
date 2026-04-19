<?php

/**
 * Contensio - The open content platform for Laravel.
 * Content approval workflow — review columns on the contents table.
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
        Schema::table('contents', function (Blueprint $table) {
            // Workflow review status — null means workflow is not active for this item.
            // Values: pending | approved | soft_rejected | hard_rejected
            $table->string('review_status', 20)->nullable()->after('status');

            // Reviewer's feedback shown to the author on rejection.
            $table->text('review_notes')->nullable()->after('review_status');

            // FK to the user who last acted as reviewer (approve / reject).
            $table->unsignedBigInteger('reviewed_by_id')->nullable()->after('review_notes');

            // When the author submitted for review.
            $table->timestamp('review_requested_at')->nullable()->after('reviewed_by_id');

            // When the reviewer last acted.
            $table->timestamp('reviewed_at')->nullable()->after('review_requested_at');

            $table->foreign('reviewed_by_id')
                ->references('id')->on('users')
                ->nullOnDelete();

            $table->index('review_status');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by_id']);
            $table->dropIndex(['review_status']);
            $table->dropColumn([
                'review_status',
                'review_notes',
                'reviewed_by_id',
                'review_requested_at',
                'reviewed_at',
            ]);
        });
    }
};
