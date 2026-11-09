<?php

namespace App\Support\Cad;

use App\Models\Call;
use App\Models\CallLog;

class CallActivityLogger
{
    private const CATEGORIES = ['CALL', 'UNIT', 'PERSON', 'VEHICLE', 'REPORT', 'NOTE', 'SYSTEM', 'SITUATION', 'DISPATCH', 'WITNESS'];

    public static function actor(): string
    {
        $unit = auth()->user()?->active_unit;

        return $unit?->log_identity ?? auth()->user()?->preferred_name ?? 'SYSTEM';
    }

    public static function record(Call $call, string $category, string $message, ?string $actor = null): CallLog
    {
        $category = strtoupper($category);
        if (!in_array($category, self::CATEGORIES, true)) {
            $category = 'SYSTEM';
        }

        $entry = CallLog::create([
            'call_id' => $call->id,
            'from' => $actor ?? self::actor(),
            'text' => '[' . $category . '] ' . $message,
        ]);

        $call->touch();

        return $entry;
    }

    public static function changes(Call $call, array $changes, ?string $actor = null): void
    {
        foreach ($changes as $label => $values) {
            [$old, $new] = $values;
            $old = trim((string) ($old ?? ''));
            $new = trim((string) ($new ?? ''));
            if ($old !== $new) {
                // Never write unlimited potentially sensitive narrative text to an audit feed.
                if (strtolower($label) === 'narrative') {
                    self::record($call, 'CALL', 'Incident narrative updated.', $actor);
                } else {
                    self::record($call, 'CALL',
                        $label . ' changed from "' . ($old === '' ? 'Not specified' : mb_strimwidth($old, 0, 120, '…')) .
                        '" to "' . ($new === '' ? 'Not specified' : mb_strimwidth($new, 0, 120, '…')) . '".',
                        $actor);
                }
            }
        }
    }
}
