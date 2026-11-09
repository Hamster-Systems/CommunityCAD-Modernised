<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('reporting_unit_name', 160)->nullable();
            $table->string('reporting_unit_callsign', 32)->nullable();
            $table->string('reporting_unit_rank', 80)->nullable();
            $table->string('reporting_unit_department', 160)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn([
                'reporting_unit_name',
                'reporting_unit_callsign',
                'reporting_unit_rank',
                'reporting_unit_department',
            ]);
        });
    }
};
