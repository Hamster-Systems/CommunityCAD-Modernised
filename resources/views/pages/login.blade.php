@extends('layouts.public')

@section('content')
<main class="cc-auth-page">
    <section class="cc-auth-card" aria-labelledby="signin-title">
        <div class="cc-auth-brand">
            <div class="cc-auth-brand-mark" aria-hidden="true">C</div>
            <div><strong>{{ get_setting('community_name') }}</strong><span>Community Management System</span></div>
        </div>
        <h1 id="signin-title">Welcome back</h1>
        <p class="cc-auth-muted">Sign in to continue to your dashboard, community tools, and account settings.</p>

        @if (session('status'))
            <div class="cc-auth-details" role="status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="cc-auth-alert" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('auth.local.login') }}">
            @csrf
            <label class="cc-auth-field" for="login">Username or email
                <input class="cc-auth-input" id="login" name="login" type="text" autocomplete="username" required value="{{ old('login') }}" placeholder="Enter your username or email">
            </label>
            <label class="cc-auth-field" for="password">Password
                <input class="cc-auth-input" id="password" name="password" type="password" autocomplete="current-password" required placeholder="Enter your password">
            </label>
            <div class="cc-auth-options">
                <label class="cc-auth-remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <a href="{{ route('auth.local.verification-help') }}">Verification help</a>
            </div>
            <button class="cc-auth-btn cc-auth-btn-primary cc-auth-submit" type="submit">Sign in</button>
        </form>

        <a class="cc-auth-btn cc-auth-btn-discord" href="{{ route('auth.discord') }}">Continue with Discord</a>

        <div class="cc-auth-divider"><span>or</span></div>
        <a class="cc-auth-btn cc-auth-btn-secondary" href="{{ route('auth.register') }}">Create an account / Apply for the community</a>

        <p class="cc-auth-footer">Secure sign-in for {{ get_setting('community_name') }}</p>
    </section>
</main>
@endsection
