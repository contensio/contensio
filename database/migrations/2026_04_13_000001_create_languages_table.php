<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->char('code', 5)->unique();            // en, fr, pt-BR
            $table->string('name', 50);                   // English, French
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->char('direction', 3)->default('ltr');  // ltr, rtl
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
