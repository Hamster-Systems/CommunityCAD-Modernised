<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Store independently supplied call postals and connected unit locations.
     * No position is inferred from unit status, call address or IP address.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('calls', 'postal')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->string('postal', 64)->nullable();
            });
        }

        if (! Schema::hasColumn('active_units', 'location')) {
            Schema::table('active_units', function (Blueprint $table) {
                $table->string('location', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('active_units', 'location')) {
            Schema::table('active_units', function (Blueprint $table) {
                $table->dropColumn('location');
            });
        }

        if (Schema::hasColumn('calls', 'postal')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->dropColumn('postal');
            });
        }
    }
};
