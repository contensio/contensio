<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            ['module' => 'core', 'setting_key' => 'site_logo',    'value' => ''],
            ['module' => 'core', 'setting_key' => 'site_favicon',  'value' => ''],
        ];

        foreach ($rows as $row) {
            DB::table('settings')->insertOrIgnore([
                'module'           => $row['module'],
                'setting_key'      => $row['setting_key'],
                'value'            => $row['value'],
                'is_translatable'  => false,
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('module', 'core')
            ->whereIn('setting_key', ['site_logo', 'site_favicon'])
            ->delete();
    }
};
