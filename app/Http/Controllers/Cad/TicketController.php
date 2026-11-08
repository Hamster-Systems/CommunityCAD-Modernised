<?php

namespace App\Http\Controllers\Cad;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cad\TicketRequest;
use App\Models\Call;
use App\Models\Charges;
use App\Models\Civilian;
use App\Models\Civilian\Vehicle;
use App\Models\License;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function show(Ticket $ticket): View
    {
        $totals = [
            'in_game_jail_time' => 0,
            'cad_jail_time' => 0,
            'fine' => 0,
        ];

        foreach ($ticket->charges as $charge) {
            $totals['in_game_jail_time'] = $totals['in_game_jail_time'] + $charge->in_game_jail_time;
            $totals['cad_jail_time'] = $totals['cad_jail_time'] + $charge->cad_jail_time;
            $totals['fine'] = $totals['fine'] + $charge->fine;
        }

        return view('cad.ticket.show', compact('ticket', 'totals'));
    }

    public function create(Civilian $civilian): View
    {
        $calls = Call::where('created_at', '>', Carbon::today()->subDays(30))->orderBy('id', 'desc')->get();

        return view('cad.ticket.create', compact('civilian', 'calls'));
    }

    public function store(Civilian $civilian, TicketRequest $request): RedirectResponse
    {
        $data = $request->validated();


        if (!empty($data['license_was_suspended'])) {
            abort_unless(!empty($data['license_id']), 422, 'Select a licence before suspending it.');
            License::whereKey($data['license_id'])->where('civilian_id', $civilian->id)->firstOrFail();
        }
        if (!empty($data['vehicle_was_impounded'])) {
            abort_unless(!empty($data['vehicle_id']), 422, 'Select a vehicle before impounding it.');
            Vehicle::whereKey($data['vehicle_id'])->firstOrFail();
        }

        $data['user_id'] = auth()->user()->id;
        $data['officer_id'] = auth()->user()->active_unit->officer_id;
        $data['civilian_id'] = $civilian->id;
        $data['offense_occured_at'] = $data['date'] . ' ' . $data['time'] . ':00';
        $data['location_of_offense'] = 'new';

        $data['court_at'] = date('Y-m-d H:i:s', strtotime('+14 Days'));

        unset($data['time'], $data['date'], $data['plate']);
        $ticket = Ticket::create($data);

        if ($data['license_was_suspended']) {
            abort_unless(!empty($data['license_id']), 422, 'Select a licence before suspending it.');
            $license = License::whereKey($data['license_id'])
                ->where('civilian_id', $civilian->id)->firstOrFail();
            $license->update(['license_status' => 3, 'suspend_ticket_id' => $ticket->id]);
        }

        if ($data['vehicle_was_impounded']) {
            abort_unless(!empty($data['vehicle_id']), 422, 'Select a vehicle before impounding it.');
            $vehicle = Vehicle::whereKey($data['vehicle_id'])->firstOrFail();
            $vehicle->update(['vehicle_status' => 3, 'impound_ticket_id' => $ticket->id]);
        }


        return redirect()->route('cad.ticket.add_charges', $ticket->id);
    }

    public function add_charges(Ticket $ticket): View
    {
        // dd($ticket->charges->penal_code_id);
        $allow_sign = true;
        if ($ticket->charges()->count() == 0) {
            $allow_sign = false;
        }

        return view('cad.ticket.edit', compact('ticket', 'allow_sign'));
    }

    public function add_charges_store(Ticket $ticket, Request $request)
    {
        if ($request->penal_code_id == 0) {
            return redirect()->route('cad.ticket.add_charges', $ticket->id)->with('alerts', [['message' => 'You must choose a charge.', 'level' => 'error']]);
        }

        $validated = $request->validate([
            'in_game_jail_time' => 'required|numeric',
            'fine' => 'required|numeric',
            'cad_jail_time' => 'required|numeric',
            'description' => 'required',
            'penal_code_id' => 'required|numeric',
            'counts' => 'required|numeric',
        ]);

        $validated['ticket_id'] = $ticket->id;

        Charges::create($validated);

        return redirect()->route('cad.ticket.add_charges', $ticket->id);
    }

    public function sign_ticket(Ticket $ticket)
    {
        abort_unless($ticket->charges()->exists(), 422, 'Add at least one charge before signing.');
        abort_unless((int) $ticket->user_id === (int) auth()->id() || auth()->user()->can('admin_access'), 403);
        $ticket->touch('updated_at');
        return redirect()->route('cad.ticket.show', $ticket->id);
    }
}
