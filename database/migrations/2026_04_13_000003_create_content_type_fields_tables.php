<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_type_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);                   // subtitle, price, color
            $table->string('field_type', 30);               // text, number, image, select...
            $table->json('options')->nullable();             // choices, min/max, file types
            $table->json('validation')->nullable();          // required, min, max, regex
            $table->boolean('is_translatable')->default(false);
            $table->boolean('is_required')->default(false);
            $table->string('group', 100)->nullable();       // tab/section name
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['content_type_id', 'name']);
            $table->index(['content_type_id', 'position']);
        });

        Schema::create('content_type_field_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_field_id')->constrained('content_type_fields')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->restrictOnDelete();
            $table->string('label');                        // Subtitle, Price
            $table->string('placeholder')->nullable();
            $table->string('help_text', 500)->nullable();

            $table->unique(['content_type_field_id', 'language_id'], 'ctft_field_language_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_type_field_translations');
        Schema::dropIfExists('content_type_fields');
    }
};
