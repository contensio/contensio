<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->char('code', 16)->unique();
            $table->foreignId('content_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('author_name', 100)->nullable(); // guest comments
            $table->string('author_email', 200)->nullable(); // guest comments
            $table->text('body');
            $table->string('status', 20)->default('pending'); // pending, approved, spam, trashed
            $table->string('ip_address', 45)->nullable();    // IPv6 max
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            // List comments for a content item
            $table->index(['content_id', 'status', 'created_at']);
            // Moderation queue
            $table->index(['status', 'created_at']);
            $table->index('parent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
