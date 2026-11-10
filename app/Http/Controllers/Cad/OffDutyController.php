<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Cad\ActiveUnit;
use App\Models\Call;
use App\Support\Cad\PanicResponseService;
use App\Support\Cad\CallUnitAssignmentService;
use App\Models\Report;
use App\Notifications\DiscordNotification;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

class OffDutyController extends Controller
{
    /**
     * EXIT must not strand a unit or its incident in PANIC. Clear the
     * emergency first using the same safe restoration service as Clear Panic,
     * then release this unit's calls (never other responders' assignments).
     */
    private function clearUnitForExit(ActiveUnit $unit): void
    {
        if ($unit->is_panic || $unit->status === 'PANIC') {
            app(PanicResponseService::class)->clear($unit);
            $unit->refresh();
        }

        foreach ($unit->calls()->get() as $call) {
            app(CallUnitAssignmentService::class)->detach($unit, $call);
        }
        $unit->refresh();
        if (! str_starts_with($unit->status, 'OFFDTY')) {
            $unit->update([
                'status' => 'AVL',
                'description' => 'Unit cleared from active calls on exit',
                'pre_panic_status' => null,
            ]);
        }
    }

    public function create()
    {
        $active_unit = ActiveUnit::where('user_id', auth()->user()->id)->first();
        if (! $active_unit) {
            return redirect()->route('portal.dashboard');
        }

        $this->clearUnitForExit($active_unit);

        session()->forget('cad.filters');
        session()->save();

        $active_unit->update(['status' => 'OFFDTY - RPT', 'off_duty_at' => now(), 'off_duty_type' => 1]);

        return view('cad.offduty.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'text' => '',
            'mdcontent' => 'required',
            'report_type_id' => 'required|numeric',
            'title' => 'required',
        ]);

        $validated['user_id'] = auth()->user()->id;
        $validated['report_type_id'] = 1;
        $validated['text'] = $validated['mdcontent'];
        unset($validated['mdcontent']);

        $report = Report::create($validated);

        $active_unit = ActiveUnit::where('user_id', auth()->user()->id)->get()->first();
        if (! $active_unit) {
            return redirect()->route('portal.dashboard');
        }
        $this->clearUnitForExit($active_unit);

        $start_timer = $active_unit->first_on_duty_at;
        $end_timer = $active_unit->off_duty_at;

        if (is_null($start_timer) || is_null($end_timer)) {
            DiscordNotification::send(
                'cad_off_duty',
                auth()->user()->preferred_name.' has went off duty.',
                null,
                15548997,
                [
                    [
                        'name' => 'Duration',
                        'value' => 'undefined',
                    ],
                    [
                        'name' => 'Off Duty At',
                        'value' => $end_timer?->format('m/d/Y H:i:s') ?? now()->format('m/d/Y H:i:s'),
                    ],
                    [
                        'name' => 'CommunityCAD account ID',
                        'value' => auth()->user()->id,
                    ],
                ]
            );
        } else {
            $duration = $start_timer->diffForHumans($end_timer, [
                'join' => ', ',
                'parts' => 3,
                'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            ]);

            DiscordNotification::send(
                'cad_off_duty',
                auth()->user()->preferred_name.' has went off duty.',
                null,
                15548997,
                [
                    [
                        'name' => 'Duration',
                        'value' => $duration,
                    ],
                    [
                        'name' => 'Off Duty At',
                        'value' => $end_timer->format('m/d/Y H:i:s'),
                    ],
                    [
                        'name' => 'Discord ID',
                        'value' => auth()->user()->id,
                    ],
                ]
            );
        }

        $active_unit->delete();

        return redirect()->route('portal.dashboard')->with('alerts', [['message' => 'Report Submitted.', 'level' => 'success']]);
    }

    public function skipreport()
    {
        $active_unit = ActiveUnit::where('user_id', auth()->user()->id)->first();
        if (! $active_unit) {
            return redirect()->route('portal.dashboard');
        }

        $this->clearUnitForExit($active_unit);

        $start_timer = $active_unit->first_on_duty_at;
        $end_timer = $active_unit->off_duty_at;

        if (is_null($start_timer) || is_null($end_timer)) {
            DiscordNotification::send(
                'cad_off_duty',
                auth()->user()->preferred_name.' has went off duty.',
                null,
                15548997,
                [
                    [
                        'name' => 'Duration',
                        'value' => 'undefined',
                    ],
                    [
                        'name' => 'Off Duty At',
                        'value' => $end_timer->format('m/d/Y H:i:s'),
                    ],
                    [
                        'name' => 'Discord ID',
                        'value' => auth()->user()->id,
                    ],
                ]
            );
        } else {
            $duration = $start_timer->diffForHumans($end_timer, [
                'join' => ', ',
                'parts' => 3,
                'syntax' => CarbonInterface::DIFF_ABSOLUTE,
            ]);

            DiscordNotification::send(
                'cad_off_duty',
                auth()->user()->preferred_name.' has went off duty.',
                null,
                15548997,
                [
                    [
                        'name' => 'Duration',
                        'value' => $duration,
                    ],
                    [
                        'name' => 'Off Duty At',
                        'value' => $end_timer->format('m/d/Y H:i:s'),
                    ],
                    [
                        'name' => 'Discord ID',
                        'value' => auth()->user()->id,
                    ],
                ]
            );
        }

        $active_unit->delete();

        return redirect()->route('portal.dashboard')->with('alerts', [['message' => 'Marked Off Duty.', 'level' => 'success']]);
    }
}
