<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminPageController extends Controller
{
    public function index(): View
    {
        // Display only aggregate information. The existing admin middleware
        // protects this route, and each shortcut is gated in the view.
        return view('admin.index', [
            'membersCount' => User::where('account_status', 3)->count(),
            'pendingAccountsCount' => User::where('account_status', 2)->count(),
            'departmentsCount' => Department::where('id', '>', 0)->count(),
        ]);
    }
}
