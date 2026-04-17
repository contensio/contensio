<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 40)->nullable()->unique()->after('name');
        });

        // Populate existing users with a unique 16-digit numeric handle
        DB::table('users')->whereNull('username')->orderBy('id')->each(function ($user) {
            do {
                $candidate = (string) random_int(1_000_000_000_000_000, 9_999_999_999_999_999);
            } while (DB::table('users')->where('username', $candidate)->exists());

            DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 40)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
