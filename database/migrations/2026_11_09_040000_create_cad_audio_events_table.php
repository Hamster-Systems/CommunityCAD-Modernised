<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cad_audio_events', function (Blueprint $table) {
            $table->id();
            $table->string('event', 32);
            $table->unsignedBigInteger('target_user_id')->nullable()->index();
            $table->unsignedBigInteger('call_id')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cad_audio_events');
    }
};
