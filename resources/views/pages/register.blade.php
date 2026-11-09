@extends('layouts.public')

@section('content')
<main class="cc-auth-page">
    <section class="cc-auth-card cc-auth-card-wide" aria-labelledby="register-title">
        <div class="cc-auth-brand">
            <div class="cc-auth-brand-mark" aria-hidden="true">C</div>
            <div><strong>{{ get_setting('community_name') }}</strong><span>Create a first-party account</span></div>
        </div>
        <h1 id="register-title">Create your account</h1>
        <p class="cc-auth-muted">Register with your own username, email address and password. You can connect Discord and Steam separately later.</p>

        @if ($errors->any())
            <div class="cc-auth-alert" role="alert">
                <strong>Please correct the following:</strong>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.local.register') }}">
            @csrf
            <label class="cc-auth-field" for="username">Username
                <input class="cc-auth-input" id="username" name="username" type="text" minlength="3" maxlength="40" pattern="[A-Za-z0-9_.-]+" autocomplete="username" required value="{{ old('username') }}" placeholder="Your username">
            </label>
            <label class="cc-auth-field" for="display_name">Display name
                <input class="cc-auth-input" id="display_name" name="display_name" type="text" maxlength="100" required value="{{ old('display_name') }}" placeholder="Name shown in the community">
            </label>
            <label class="cc-auth-field" for="email">Email address
                <input class="cc-auth-input" id="email" name="email" type="email" maxlength="255" autocomplete="email" required value="{{ old('email') }}" placeholder="you@example.com">
            </label>
            <label class="cc-auth-field" for="password">Password
                <input class="cc-auth-input" id="password" name="password" type="password" minlength="12" autocomplete="new-password" required placeholder="Use at least 12 characters">
            </label>
            <label class="cc-auth-field" for="password_confirmation">Confirm password
                <input class="cc-auth-input" id="password_confirmation" name="password_confirmation" type="password" minlength="12" autocomplete="new-password" required placeholder="Repeat your password">
            </label>
            <p class="cc-auth-help">Passwords must contain upper and lowercase letters and a number. Email verification is required before signing in.</p>
            <button class="cc-auth-btn cc-auth-btn-primary mt-6" type="submit">Create account</button>
        </form>

        <div class="cc-auth-divider"><span>Or use the existing Discord registration</span></div>
        <a class="cc-auth-btn cc-auth-btn-secondary" href="{{ session()->has('id') ? route('account.create') : route('auth.discord') }}">
            Continue registration with Discord
        </a>
        <p class="cc-auth-footer">Account registration does not automatically grant community membership or CAD permissions.</p>
        <p class="mt-3 text-center text-sm"><a class="text-sky-300 underline" href="{{ route('auth.login') }}">Already registered? Sign in</a></p>
    </section>
</main>
@endsection
