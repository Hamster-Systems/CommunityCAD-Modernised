<div wire:poll.10s class="text-white">
    <div class="pb-3 border-b border-slate-700 mb-3">
        <form action="{{ route('cad.call_log.store', $call->id) }}" class="" method="POST">
            @csrf
            <div class="flex items-center">
                <input autofocus class="text-input block w-full uppercase" name="text" placeholder="Add a call note…" maxlength="2000" required type="text">
                <button class="secondary-button-md ml-2 block bg-gray-600 mt-4 hover:bg-gray-500 text-white"
                    type="submit">Send</button>
            </div>
        </form>
    </div>

    <div class="max-h-[65vh] overflow-y-auto overflow-x-hidden pr-1">
        <div class="space-y-1">
            @forelse ($call_chat as $chat)
                @php
                    $text_color = 'text-gray-400';
                    if ($chat->from != 'SYSTEM') {
                        $text_color = 'text-white';
                    }
                @endphp
                <article class="mb-2 rounded-lg border {{ $loop->first ? 'border-indigo-500' : 'border-slate-700' }} bg-slate-800/70 p-3">
                    <div class="flex flex-wrap justify-between gap-2 border-b border-slate-700 pb-1">
                        <p class="font-semibold {{ $text_color }}">{{ $chat->from }}</p>
                        <time class="text-xs text-slate-400">{{ $chat->created_at->format('d M Y H:i:s') }}</time>
                    </div>
                    <p class="mt-2 whitespace-pre-wrap break-words text-sm {{ $text_color }}">{{ $chat->text }}</p>
                </article>
            @empty
                <p class="p-2">No activity recorded for this call yet.</p>
            @endforelse
        </div>
    </div>

</div>
