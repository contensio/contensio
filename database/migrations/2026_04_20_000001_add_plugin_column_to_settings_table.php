<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('plugin', 100)->nullable()->after('module')->index();
        });

        // Backfill: existing plugin_options rows store the plugin name in setting_key
        DB::table('settings')
            ->where('module', 'plugin_options')
            ->whereNull('plugin')
            ->eachById(function ($row) {
                DB::table('settings')
                    ->where('id', $row->id)
                    ->update(['plugin' => $row->setting_key]);
            });

        // Backfill: existing theme_options rows store the theme name in setting_key
        DB::table('settings')
            ->where('module', 'theme_options')
            ->whereNull('plugin')
            ->eachById(function ($row) {
                DB::table('settings')
                    ->where('id', $row->id)
                    ->update(['plugin' => $row->setting_key]);
            });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex(['plugin']);
            $table->dropColumn('plugin');
        });
    }
};
