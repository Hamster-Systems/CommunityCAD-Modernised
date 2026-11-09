<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('calls', function (Blueprint $table) {
            $table->string('pre_panic_status', 32)->nullable();
        });
        Schema::table('active_units', function (Blueprint $table) {
            $table->string('pre_panic_status', 32)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('active_units', fn (Blueprint $table) => $table->dropColumn('pre_panic_status'));
        Schema::table('calls', fn (Blueprint $table) => $table->dropColumn('pre_panic_status'));
    }
};
