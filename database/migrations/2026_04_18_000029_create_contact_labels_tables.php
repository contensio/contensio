<?php

/**
 * Contensio - The open content platform for Laravel.
 * https://contensio.com
 *
 * @copyright   Copyright (c) 2026 Iosif Gabriel Chimilevschi
 * @license     https://www.gnu.org/licenses/agpl-3.0.txt  AGPL-3.0-or-later
 * @author      Iosif Gabriel Chimilevschi <office@contensio.com>
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_labels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('color', 7)->default('#6366f1'); // hex color
            $table->string('slug', 100)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('contact_message_label', function (Blueprint $table) {
            $table->unsignedBigInteger('message_id');
            $table->unsignedBigInteger('label_id');
            $table->primary(['message_id', 'label_id']);
            $table->foreign('message_id')->references('id')->on('contact_messages')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('contact_labels')->onDelete('cascade');
        });

        // Seed default labels
        $now = now();
        DB::table('contact_labels')->insert([
            ['name' => 'Important',  'color' => '#ef4444', 'slug' => 'important',  'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Follow-up',  'color' => '#f59e0b', 'slug' => 'follow-up',  'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Sales lead', 'color' => '#10b981', 'slug' => 'sales-lead', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_message_label');
        Schema::dropIfExists('contact_labels');
    }
};
