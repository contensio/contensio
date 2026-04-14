<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Which plugin created this role, if any. Null = core or custom role.
            // Combined with is_system: (is_system=true, plugin_name=null) = core role
            //                         (plugin_name=not null)              = plugin role
            //                         (is_system=false, plugin_name=null) = custom role
            $table->string('plugin_name', 100)->nullable()->after('is_system');
            $table->index('plugin_name');
        });

        Schema::table('permissions', function (Blueprint $table) {
            // Human-readable description shown in the admin Role editor.
            $table->string('description', 255)->nullable()->after('name');
            // Which plugin declared this permission, if any. Null = core permission.
            $table->string('plugin_name', 100)->nullable()->after('description');
            $table->index('plugin_name');
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['plugin_name']);
            $table->dropColumn(['description', 'plugin_name']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['plugin_name']);
            $table->dropColumn('plugin_name');
        });
    }
};
