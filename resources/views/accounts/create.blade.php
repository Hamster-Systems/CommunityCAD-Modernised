@extends('layouts.public')

@section('content')
    <main class="cc-auth-page">
        <section class="cc-auth-card cc-auth-card-wide" aria-labelledby="register-title">
            <div class="cc-auth-brand">
                <div class="cc-auth-brand-mark" aria-hidden="true">C</div>
                <div><strong>{{ get_setting('community_name') }}</strong><span>Community account registration</span></div>
            </div>
            <h1 id="register-title">Create your account</h1>
            <p class="cc-auth-muted">Complete your community profile below. Registration does not automatically grant CAD access.</p>

            @if (!session()->has('id'))
                <div class="cc-auth-details">
                    <p>Start by connecting Discord to verify your identity. You'll return here to complete the form.</p>
                </div>
                <a class="cc-auth-btn cc-auth-btn-primary" href="{{ route('auth.discord') }}">Connect Discord to register</a>
            @else
                <div class="cc-auth-details">
                    <p><strong class="text-white">Discord connected:</strong> {{ session('discord_name') }}</p>
                    <p class="mt-1">Your Discord account is used to identify your account in this version of CommunityCAD.</p>
                </div>
                @if ($errors->any())
                    <div class="cc-auth-alert" role="alert">
                        <p class="font-bold">Please correct the following:</p>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('account.store') }}">
                    @csrf
                    <label class="cc-auth-field" for="display_name">Display name
                        <input class="cc-auth-input" id="display_name" name="display_name" type="text" maxlength="255" autocomplete="nickname" value="{{ old('display_name') }}" placeholder="Your community display name">
                    </label>
                    <label class="cc-auth-field" for="email">Email address
                        <input class="cc-auth-input" id="email" name="email" type="email" autocomplete="email" required maxlength="255" value="{{ old('email') }}" placeholder="you@example.com">
                    </label>
                    <label class="cc-auth-field" for="birthday">Date of birth
                        <input class="cc-auth-input" id="birthday" name="birthday" type="date" autocomplete="bday" required value="{{ old('birthday') }}">
                    </label>
                    <div class="cc-auth-details">
                        <p class="font-bold text-white">Steam account</p>
                        @if (session('steam_id'))
                            <p class="mt-1">Linked: {{ session('steam_username') }} ({{ session('steam_id') }})</p>
                        @else
                            <p class="mt-1">{{ get_setting('force_steam_link') ? 'This community requires Steam during registration.' : 'You can optionally link Steam before creating your account.' }}</p>
                            <a class="mt-3 inline-block font-semibold text-sky-300 underline hover:text-white" href="{{ route('auth.steam') }}">Connect Steam account</a>
                        @endif
                    </div>
                    <button class="cc-auth-btn cc-auth-btn-primary" type="submit">Create account</button>
                </form>
            @endif
            <div class="cc-auth-divider"><span>Already have an account?</span></div>
            <a href="{{ route('auth.login') }}" class="cc-auth-btn cc-auth-btn-secondary">Back to sign in</a>
            <p class="cc-auth-footer">Account creation and membership approval are separate processes.</p>
        </section>
    </main>
@endsection
