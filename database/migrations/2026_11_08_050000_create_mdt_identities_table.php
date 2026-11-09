<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mdt_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('user_department_id')->constrained('user_departments');
            $table->foreignId('civilian_id')->nullable()->constrained('civilians')->nullOnDelete();
            $table->string('display_name', 100);
            $table->string('callsign', 32);
            $table->string('rank', 80);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'user_department_id']);
        });

        Schema::table('active_units', function (Blueprint $table) {
            $table->foreignId('mdt_identity_id')->nullable()->constrained('mdt_identities')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('active_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('mdt_identity_id');
        });
        Schema::dropIfExists('mdt_identities');
    }
};
