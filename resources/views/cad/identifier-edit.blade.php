@extends('layouts.cad_simple')

@section('content')
<section class="min-h-screen flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-3xl card">
        <div class="mb-6">
            <a href="{{ route('cad.landing', [], false) }}"
               class="text-sm text-gray-300 underline hover:text-white">&larr; Back to identifier selection</a>
            <h1 class="mt-4 text-2xl font-semibold">Edit Current Identifier</h1>
            <p class="mt-2 text-sm text-gray-300">
                Update your name, department, callsign, rank, linked civilian and subdivisions.
                Changes to an active unit take effect next time you sign in.
            </p>
        </div>
        @if ($errors->any())
            <div role="alert" class="mb-5 rounded-lg border border-red-500 bg-red-950/30 p-4 text-red-200">
                <p class="font-semibold mb-2">Please review these details:</p>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        @include('cad.partials.identity-form', ['editing' => true])
    </div>
    @include('cad.partials.identity-form-script')
</section>
@endsection
