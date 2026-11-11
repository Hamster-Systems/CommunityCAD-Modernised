<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->json('mdt_subdivisions')->nullable();
        });

        // Preserve the options displayed to existing units, but allow each
        // department to manage its own set independently from now on.
        $legacy = array_values(config('cad.mdt_subdivisions', []));
        DB::table('departments')->update(['mdt_subdivisions' => json_encode($legacy)]);
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('mdt_subdivisions');
        });
    }
};
