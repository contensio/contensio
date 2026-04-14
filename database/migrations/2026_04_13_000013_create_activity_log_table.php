<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30);                   // created, updated, deleted, published, login
            $table->string('subject_type', 100);             // content, user, role, media, menu
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description', 500)->nullable();
            $table->json('properties')->nullable();          // old/new values for audit
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at');

            // Recent activity for a user
            $table->index(['user_id', 'created_at']);
            // Activity on a specific subject
            $table->index(['subject_type', 'subject_id']);
            // Dashboard: recent activity
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
