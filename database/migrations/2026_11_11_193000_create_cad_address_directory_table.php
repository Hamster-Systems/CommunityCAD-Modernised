<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cad_address_directory', function (Blueprint $table) {
            $table->id();
            $table->string('street', 255);
            $table->string('city', 255)->nullable();
            $table->string('postal', 64)->nullable();
            $table->string('search_key', 64)->unique();
            $table->timestamps();
            $table->index('street');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cad_address_directory');
    }
};
