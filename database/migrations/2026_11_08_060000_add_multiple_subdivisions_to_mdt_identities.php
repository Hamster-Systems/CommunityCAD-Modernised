<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mdt_identities', function (Blueprint $table) {
            $table->json('subdivisions')->nullable();
        });

        // Preserve previously selected single subdivisions when upgrading.
        DB::table('mdt_identities')->whereNotNull('subdivision')->where('subdivision', '!=', '')
            ->orderBy('id')->chunkById(100, function ($identities) {
                foreach ($identities as $identity) {
                    DB::table('mdt_identities')->where('id', $identity->id)
                        ->update(['subdivisions' => json_encode([$identity->subdivision])]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('mdt_identities', function (Blueprint $table) {
            $table->dropColumn('subdivisions');
        });
    }
};
