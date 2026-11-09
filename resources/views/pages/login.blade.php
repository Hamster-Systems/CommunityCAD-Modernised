@extends('layouts.public')

@section('content')
    <main class="cc-auth-page">
        <section class="cc-auth-card" aria-labelledby="signin-title">
            <div class="cc-auth-brand">
                <div class="cc-auth-brand-mark" aria-hidden="true">C</div>
                <div><strong>{{ get_setting('community_name') }}</strong><span>Community Management System</span></div>
            </div>
            <h1 id="signin-title">Welcome back</h1>
            <p class="cc-auth-muted">Sign in to access your community dashboard, profile and authorised CAD tools.</p>
            <a class="cc-auth-btn cc-auth-btn-primary" href="{{ route('auth.discord') }}">Continue with Discord</a>
            <p class="cc-auth-help">Discord is currently the supported sign-in method. Password login is planned.</p>
            <div class="cc-auth-divider"><span>New to the community?</span></div>
            <a class="cc-auth-btn cc-auth-btn-secondary" href="{{ route('auth.register') }}">Create an account</a>
            <p class="cc-auth-footer">A community account does not automatically grant CAD membership.</p>
        </section>
    </main>
@endsection
