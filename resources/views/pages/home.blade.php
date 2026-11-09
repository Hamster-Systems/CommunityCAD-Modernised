@extends('layouts.public')

@section('content')
    <main class="mx-auto w-full max-w-6xl px-4 pb-16 pt-10 text-white sm:px-6 sm:pt-16">
        <section class="overflow-hidden rounded-2xl border border-slate-600 bg-[#124559] shadow-xl">
            <div class="border-b border-slate-500 px-6 py-8 sm:px-10 sm:py-10">
                <p class="mb-3 text-xs font-bold uppercase tracking-widest text-sky-200">{{ get_setting('community_name') }}</p>
                @auth
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Welcome back, {{ auth()->user()->preferred_name }}!
                    </h1>
                    <p class="mt-3 max-w-2xl text-slate-200">Access your community account, applications and CAD portal from one place.</p>
                @else
                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        Welcome to {{ get_setting('community_name') }}!
                    </h1>
                    <div class="mt-4 max-w-3xl text-slate-200">
                        @if (filled(get_setting('community_intro')))
                            {!! Str::markdown(get_setting('community_intro')) !!}
                        @else
                            <p>Your community portal for applications, account details and CAD access.</p>
                        @endif
                    </div>
                @endauth
            </div>

            @auth
                <div class="grid gap-4 p-6 sm:p-10 md:grid-cols-2">
                    @if (auth()->user()->account_status === 3)
                        <a href="{{ route('portal.dashboard') }}"
                           class="group rounded-xl border border-sky-600 bg-sky-900 p-6 transition hover:border-sky-300 hover:bg-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-300">
                            <h2 class="text-xl font-bold">Enter CAD Portal</h2>
                            <p class="mt-2 text-sm text-slate-200">Open your community dashboard and access your authorised tools.</p>
                            <span class="mt-5 inline-block text-sm font-semibold text-sky-200 group-hover:text-white">Open portal →</span>
                        </a>
                    @endif

                    <a href="{{ route('account.show', auth()->user()->id) }}"
                       class="group rounded-xl border border-slate-500 bg-slate-800 p-6 transition hover:border-sky-400 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-sky-300">
                        <h2 class="text-xl font-bold">My Profile</h2>
                        <p class="mt-2 text-sm text-slate-200">View your account and community profile information.</p>
                        <span class="mt-5 inline-block text-sm font-semibold text-sky-300 group-hover:text-white">View profile →</span>
                    </a>

                    @if (auth()->user()->account_status === 1 && get_setting('members_must_apply') == 'true')
                        <div class="rounded-xl border border-slate-500 bg-slate-800 p-6">
                            <h2 class="text-xl font-bold">Community application</h2>
                            @if (is_null(auth()->user()->reapply) || (auth()->user()->reapply && auth()->user()->reapply_date && auth()->user()->reapply_date <= now()))
                                <p class="mt-2 text-sm text-slate-200">Apply to join the community.</p>
                                <a href="{{ route('application.create') }}"
                                   class="mt-5 inline-block rounded-lg bg-sky-700 px-4 py-2 font-semibold text-white hover:bg-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-300">New application</a>
                            @elseif (auth()->user()->reapply && auth()->user()->reapply_date > now())
                                <p class="mt-2 text-sm text-slate-200">You can reapply after {{ auth()->user()->reapply_date->format('m/d/Y') }}. Review your previous application for details.</p>
                            @else
                                <p class="mt-2 text-sm text-slate-200">You are not currently eligible to reapply. Review your previous application for details.</p>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-500 px-6 py-5 sm:px-10">
                    <p class="text-sm text-slate-200">Signed in as {{ auth()->user()->preferred_name }}</p>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="rounded-lg border border-red-400 px-4 py-2 text-sm font-semibold text-red-100 transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300">Log out</button>
                    </form>
                </div>
            @else
                <div class="grid gap-6 p-6 sm:p-10 lg:grid-cols-5">
                    <div class="rounded-xl border border-sky-500 bg-slate-800 p-6 lg:col-span-3">
                        <p class="text-xs font-semibold uppercase tracking-widest text-sky-300">New to the community?</p>
                        <h2 class="mt-3 text-2xl font-bold">Create your CommunityCAD account</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-200">
                            Get started by setting up a community account. You can then complete your profile,
                            apply to join and access the services you're approved for.
                        </p>
                        <div class="mt-6 rounded-lg border border-slate-600 bg-slate-900 p-4 text-sm text-slate-200">
                            <p class="font-semibold text-white">How joining works</p>
                            <ol class="mt-3 list-inside list-decimal space-y-2">
                                <li>Connect with Discord to begin registration.</li>
                                <li>Complete your CommunityCAD account details.</li>
                                <li>Apply for membership if your community requires it.</li>
                            </ol>
                        </div>
                        @if (session()->has('id'))
                            <a href="{{ route('account.create') }}"
                               class="mt-6 inline-flex items-center rounded-lg bg-sky-600 px-5 py-3 font-bold text-white transition hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
                                Continue account registration →
                            </a>
                        @else
                            <a href="{{ route('auth.discord') }}"
                               class="mt-6 inline-flex items-center rounded-lg bg-sky-600 px-5 py-3 font-bold text-white transition hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-200">
                                Create account with Discord →
                            </a>
                        @endif
                        <p class="mt-3 text-xs text-slate-300">Discord is currently required for initial registration. Email/password registration is being developed.</p>
                    </div>

                    <div class="rounded-xl border border-slate-500 bg-slate-800 p-6 lg:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-widest text-slate-300">Already registered?</p>
                        <h2 class="mt-3 text-xl font-bold">Welcome back</h2>
                        <p class="mt-3 text-sm leading-relaxed text-slate-200">
                            Have an existing CommunityCAD account? Sign in to return to your profile or CAD portal.
                        </p>
                        <a href="{{ route('auth.discord') }}"
                           class="mt-6 inline-flex items-center rounded-lg border border-slate-400 bg-slate-700 px-5 py-3 font-semibold text-white transition hover:border-sky-300 hover:bg-slate-600 focus:outline-none focus:ring-2 focus:ring-sky-200">
                            Sign in with Discord
                        </a>
                        <div class="mt-7 border-t border-slate-600 pt-5">
                            <h3 class="text-sm font-semibold text-white">Connected accounts are coming</h3>
                            <p class="mt-2 text-sm text-slate-300">
                                We plan to add optional Discord and Steam linking once first-party account registration is ready.
                            </p>
                        </div>
                    </div>
                </div>
            @endauth

            <footer class="border-t border-slate-500 px-6 py-4 text-sm text-slate-300 sm:px-10">
                <a class="underline decoration-slate-400 underline-offset-4 hover:text-white focus:outline-none focus:ring-2 focus:ring-sky-300"
                   href="https://communitycad.app">CommunityCAD {{ config('app.version') }}</a>
            </footer>
        </section>
    </main>
@endsection
