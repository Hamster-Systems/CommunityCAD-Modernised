@extends('layouts.public')

@section('content')
<main class="cc-auth-page">
    <section class="cc-auth-card" aria-labelledby="signin-title">
        <div class="cc-auth-brand">
            <div class="cc-auth-brand-mark" aria-hidden="true">C</div>
            <div><strong>{{ get_setting('community_name') }}</strong><span>Community Management System</span></div>
        </div>
        <h1 id="signin-title">Welcome back</h1>
        <p class="cc-auth-muted">Sign in to your community dashboard and account.</p>
        @if (session('status'))<div class="cc-auth-details" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())
            <div class="cc-auth-alert" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('auth.local.login') }}">
            @csrf
            <label class="cc-auth-field" for="login">Username or email
                <input class="cc-auth-input" id="login" name="login" type="text" autocomplete="username" required value="{{ old('login') }}" placeholder="Username or email address">
            </label>
            <label class="cc-auth-field" for="password">Password
                <input class="cc-auth-input" id="password" name="password" type="password" autocomplete="current-password" required placeholder="Your password">
            </label>
            <label class="mt-4 flex items-center gap-2 text-sm text-slate-200">
                <input type="checkbox" name="remember" value="1"> Keep me signed in
            </label>
            <button class="cc-auth-btn cc-auth-btn-primary mt-5" type="submit">Sign in</button>
        </form>
        <div class="cc-auth-divider"><span>or</span></div>
        <a class="cc-auth-btn cc-auth-btn-secondary" href="{{ route('auth.discord') }}">Continue with Discord</a>
        <div class="cc-auth-details">
            <p class="font-semibold text-white">Need another verification email?</p>
            <form class="mt-3" action="{{ route('auth.local.resend') }}" method="POST">
                @csrf
                <label class="cc-auth-field" for="resend_login">Username or email
                    <input class="cc-auth-input" id="resend_login" name="login" type="text" required autocomplete="username" placeholder="Username or email">
                </label>
                <label class="cc-auth-field" for="resend_password">Password
                    <input class="cc-auth-input" id="resend_password" name="password" type="password" required autocomplete="current-password" placeholder="Account password">
                </label>
                <button class="cc-auth-btn cc-auth-btn-secondary mt-3" type="submit">Resend verification email</button>
            </form>
        </div>
        <div class="cc-auth-divider"><span>New here?</span></div>
        <a class="cc-auth-btn cc-auth-btn-secondary" href="{{ route('auth.register') }}">Create an account</a>
        <p class="cc-auth-footer">A CommunityCAD account does not automatically grant CAD access.</p>
    </section>
</main>
@endsection
