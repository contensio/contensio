<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            // active   = shown in admin and frontend
            // inactive = shown in admin only (content being prepared)
            // disabled = hidden everywhere
            $table->string('status', 10)->default('active')->after('is_default');
        });

        // Migrate existing boolean is_active → status
        DB::table('languages')->where('is_active', true)->update(['status' => 'active']);
        DB::table('languages')->where('is_active', false)->update(['status' => 'disabled']);

        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('is_default');
        });

        DB::table('languages')->where('status', 'active')->update(['is_active' => true]);
        DB::table('languages')->where('status', '!=', 'active')->update(['is_active' => false]);

        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
