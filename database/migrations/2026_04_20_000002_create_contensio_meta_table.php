<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contensio_meta', function (Blueprint $table) {
            $table->id();
            $table->string('metable_type', 255);
            $table->unsignedBigInteger('metable_id');
            $table->string('meta_key', 255);
            $table->longText('meta_value')->nullable();
            $table->string('plugin', 200)->nullable()->index()
                  ->comment('Composer package name - used for cleanup on uninstall');
            $table->timestamps();

            $table->index(['metable_type', 'metable_id'], 'metable_index');
            $table->index(['metable_type', 'metable_id', 'meta_key'], 'metable_key_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contensio_meta');
    }
};
