<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mdt_identities', function (Blueprint $table) {
            $table->string('subdivision', 80)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mdt_identities', function (Blueprint $table) {
            $table->dropColumn('subdivision');
        });
    }
};
