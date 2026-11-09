<?php

use App\Models\Call;
use App\Support\Cad\CallAssignmentStatusService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Repair existing open-response incidents that have no attached units.
        // Do not change held, cleared, or closed incidents.
        Call::query()
            ->whereIn('status', ['ENRT', 'ARRVD'])
            ->whereDoesntHave('attached_units')
            ->chunkById(100, function ($calls) {
                foreach ($calls as $call) {
                    DB::transaction(fn () =>
                        CallAssignmentStatusService::reopenIfUnassigned($call));
                }
            });
    }

    public function down(): void
    {
        // Intentionally irreversible: the prior response state cannot
        // reliably be inferred after an orphaned incident is repaired.
    }
};
