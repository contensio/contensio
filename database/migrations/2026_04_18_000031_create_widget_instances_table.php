<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('widget_instances', function (Blueprint $table) {
            $table->id();
            $table->string('area_id', 80);           // e.g. 'sidebar', 'after-post'
            $table->string('widget_type', 80);       // e.g. 'latest-posts', 'tag-cloud'
            $table->unsignedSmallInteger('position')->default(0);
            $table->json('config')->nullable();      // instance-specific config
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['area_id', 'is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('widget_instances');
    }
};
