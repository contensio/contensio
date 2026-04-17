<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('session_id', 255)->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
