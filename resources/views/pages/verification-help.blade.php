@extends('layouts.public')

@section('content')
<main class="cc-auth-page">
    <section class="cc-auth-card" aria-labelledby="verification-title">
        <div class="cc-auth-brand">
            <div class="cc-auth-brand-mark" aria-hidden="true">C</div>
            <div><strong>{{ get_setting('community_name') }}</strong><span>Account assistance</span></div>
        </div>
        <h1 id="verification-title">Email verification</h1>
        <p class="cc-auth-muted">Didn't receive your verification email? Confirm your account details below to request another link.</p>
        @if (session('status'))<div class="cc-auth-details" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())
            <div class="cc-auth-alert" role="alert">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        <form action="{{ route('auth.local.resend') }}" method="POST">
            @csrf
            <label class="cc-auth-field" for="resend_login">Username or email
                <input class="cc-auth-input" id="resend_login" name="login" type="text" autocomplete="username" required value="{{ old('login') }}" placeholder="Username or email">
            </label>
            <label class="cc-auth-field" for="resend_password">Password
                <input class="cc-auth-input" id="resend_password" name="password" type="password" autocomplete="current-password" required placeholder="Account password">
            </label>
            <button class="cc-auth-btn cc-auth-btn-primary cc-auth-submit" type="submit">Resend verification email</button>
        </form>
        <p class="cc-auth-help">For security, this form won't reveal whether an account exists.</p>
        <div class="cc-auth-divider"><span>Back to your account</span></div>
        <a class="cc-auth-btn cc-auth-btn-secondary" href="{{ route('auth.login') }}">Back to sign in</a>
    </section>
</main>
@endsection
