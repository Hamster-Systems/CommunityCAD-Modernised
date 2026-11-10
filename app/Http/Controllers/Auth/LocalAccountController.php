<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LocalAccountCredential;
use App\Models\User;
use App\Notifications\VerifyLocalAccountEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class LocalAccountController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Za-z0-9_.-]+$/', Rule::unique('local_account_credentials', 'username')],
            'display_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('local_account_credentials', 'email'), Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $email = strtolower(trim($validated['email']));
        $username = strtolower(trim($validated['username']));

        // Existing Discord users own their records. Do not auto-merge email
        // addresses or change any existing primary keys / permissions.
        if (User::whereRaw('LOWER(email) = ?', [$email])->exists()) {
            return back()->withErrors(['email' => 'An account already uses this email address. Sign in using your existing method.'])->withInput($request->except('password', 'password_confirmation'));
        }

        $user = DB::transaction(function () use ($validated, $email, $username) {
            // users.id is already AUTO_INCREMENT in the original schema.
            // insertGetId allocates a unique PK without renumbering legacy Discord IDs.
            $userId = DB::table('users')->insertGetId([
                'display_name' => trim($validated['display_name']),
                'email' => $email,
                'account_status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            LocalAccountCredential::create([
                'user_id' => $userId,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($validated['password']),
            ]);

            return User::findOrFail($userId);
        });

        try {
            Notification::route('mail', $email)
                ->notify(new VerifyLocalAccountEmail((int) $user->id, $email));
        } catch (\Throwable $exception) {
            // The account already exists: never present an email transport
            // failure as a failed registration that users should retry.
            report($exception);

            return redirect()->route('auth.local.verification-help')
                ->with('status', 'Your account was created, but the verification email could not be sent. Check mail configuration or contact an administrator, then request a new email here.');
        }

        return redirect()->route('auth.login')
            ->with('status', 'Account created. Check your email to verify it before signing in.');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identifier = strtolower(trim($validated['login']));
        $credential = LocalAccountCredential::query()
            ->where('username', $identifier)->orWhere('email', $identifier)->first();

        if (!$credential || !Hash::check($validated['password'], $credential->password)) {
            return back()->withErrors(['login' => 'These credentials do not match an account.'])->onlyInput('login');
        }

        if ($credential->email_verified_at === null) {
            return back()->withErrors(['login' => 'Verify your email before signing in. Use the verification email sent during registration.'])->onlyInput('login');
        }

        $user = $credential->user;
        if (!$user || in_array((int) $user->account_status, [5, 6], true)) {
            return back()->withErrors(['login' => 'This account cannot sign in.'])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->forceFill(['last_login' => now()])->save();

        return redirect()->intended((int) $user->account_status === 3
            ? route('portal.dashboard')
            : route('home'));
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        $credential = $user->localCredential;
        abort_unless($request->hasValidSignature()
            && $credential
            && hash_equals(sha1(strtolower($credential->email)), (string) $request->query('hash')), 403);

        if ($credential->email_verified_at === null) {
            $credential->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->route('auth.login')
            ->with('status', 'Email verified. You can now sign in.');
    }

    public function resend(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identifier = strtolower(trim($validated['login']));
        $credential = LocalAccountCredential::query()
            ->where('username', $identifier)->orWhere('email', $identifier)->first();

        if ($credential && $credential->email_verified_at === null
            && Hash::check($validated['password'], $credential->password)) {
            Notification::route('mail', $credential->email)
                ->notify(new VerifyLocalAccountEmail((int) $credential->user_id, $credential->email));
        }

        return back()->with('status', 'If your unverified account credentials were correct, a verification email has been sent.');
    }
}
