<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Report;
use App\Models\ReportType;
use App\Support\Cad\CallActivityLogger;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function create(): View
    {
        $report_types = ReportType::where('id', '!=', 1)->get();
        $calls = Call::where('updated_at', '>', Carbon::now()->subDays(30)->format('Y-m-d 00:00:00'))->get();

        return view('cad.report.create', compact('report_types', 'calls'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mdcontent' => ['required', 'string', 'max:50000'],
            'title' => ['required', 'string', 'max:200'],
            'call_id' => ['nullable', 'integer', 'exists:calls,id'],
            'report_type_id' => ['required', 'integer', 'exists:report_types,id'],
        ]);

        $data = $validated;
        $data['text'] = $data['mdcontent'];
        unset($data['mdcontent']);
        $user = $request->user();
        $unit = $user->active_unit;
        $data['user_id'] = $user->id;
        // Legacy Officer rows are optional for independent MDT identities.
        $data['officer_id'] = $unit?->officer_id;

        // Capture the unit identity at submission time. Later identity edits,
        // department transfers, or off-duty transitions must not rewrite reports.
        $data['reporting_unit_name'] = mb_substr(
            $unit?->officer_name ?? $user->preferred_name ?? 'Unknown author', 0, 160
        );
        $data['reporting_unit_callsign'] = $unit ? mb_substr($unit->unit_callsign, 0, 32) : null;
        $data['reporting_unit_rank'] = $unit ? mb_substr($unit->unit_rank, 0, 80) : null;
        $data['reporting_unit_department'] = $unit?->user_department?->department?->name
            ? mb_substr($unit->user_department->department->name, 0, 160)
            : null;

        $report = Report::create($data);

        if ($report->call_id && ($linkedCall = Call::find($report->call_id))) {
            CallActivityLogger::record($linkedCall, 'REPORT', 'Report #' . $report->id . ' linked: ' . $report->title . '.');
        }

        return redirect()->route('cad.report.show', $report->id);
    }

    public function show(Report $report): View
    {
        return view('cad.report.show', compact('report'));
    }

    public function edit(Report $report): View
    {
        abort_unless((int) $report->user_id === (int) auth()->id() || auth()->user()->can('admin_access'), 403);
        $report_types = ReportType::all();
        $calls = Call::where('updated_at', '>', Carbon::now()->subDays(30)->format('Y-m-d 00:00:00'))->get();

        return view('cad.report.edit', compact('report', 'report_types', 'calls'));
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        abort_unless((int) $report->user_id === (int) auth()->id() || auth()->user()->can('admin_access'), 403);
        $validated = $request->validate([
            'mdcontent' => ['required', 'string', 'max:50000'],
            'title' => ['required', 'string', 'max:200'],
            'call_id' => ['nullable', 'integer', 'exists:calls,id'],
            'report_type_id' => ['required', 'integer', 'exists:report_types,id'],
        ]);

        $data = $validated;
        $data['text'] = $data['mdcontent'];
        unset($data['mdcontent']);

        $previousCallId = $report->call_id;
        $previousTitle = $report->title;
        $report->update($data);

        if ((string) ($previousCallId ?? '') !== (string) ($report->call_id ?? '')) {
            if ($previousCallId && ($previousCall = Call::find($previousCallId))) {
                CallActivityLogger::record($previousCall, 'REPORT', 'Report #' . $report->id . ' unlinked.');
            }
            if ($report->call_id && ($currentCall = Call::find($report->call_id))) {
                CallActivityLogger::record($currentCall, 'REPORT', 'Report #' . $report->id . ' linked: ' . $report->title . '.');
            }
        } elseif ($report->call_id && ($linkedCall = Call::find($report->call_id))) {
            CallActivityLogger::record($linkedCall, 'REPORT',
                'Linked report #' . $report->id . ($previousTitle !== $report->title ? ' renamed to ' . $report->title : ' updated') . '.');
        }

        return redirect()->route('cad.report.show', $report->id);
    }
}
