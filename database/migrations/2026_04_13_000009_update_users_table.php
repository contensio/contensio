<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->char('code', 16)->unique()->after('id');
            $table->string('avatar', 500)->nullable()->after('email');
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete()->after('avatar');
            $table->boolean('is_active')->default(true)->after('language_id');
        });

        Schema::create('user_meta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->foreignId('language_id')->nullable()->constrained('languages')->restrictOnDelete();

            $table->index(['user_id', 'meta_key', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_meta');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropColumn(['code', 'avatar', 'language_id', 'is_active']);
        });
    }
};
