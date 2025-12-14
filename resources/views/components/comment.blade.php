<div class="flex gap-4 {{ $depth > 0 ? 'ml-8 pt-4 border-l-2 border-gray-100 pl-4' : '' }}">
    {{-- Avatar --}}
    <div class="flex-shrink-0">
        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
            <span class="text-gray-600 font-medium text-sm">
                {{ strtoupper(substr($comment->user?->name ?? $comment->guest_name ?? 'G', 0, 1)) }}
            </span>
        </div>
    </div>

    <div class="flex-1 min-w-0">
        {{-- Header --}}
        <div class="flex items-center gap-2 text-sm">
            <span class="font-medium text-gray-900">
                {{ $comment->user?->name ?? $comment->guest_name ?? 'Guest' }}
            </span>
            <span class="text-gray-500">
                {{ $comment->created_at->diffForHumans() }}
            </span>
            @if($comment->created_at != $comment->updated_at)
                <span class="text-gray-400 text-xs">(edited)</span>
            @endif
        </div>

        {{-- Body --}}
        <div class="mt-1 text-gray-700 prose prose-sm max-w-none">
            {!! nl2br(e($comment->body)) !!}
        </div>

        {{-- Slot for custom actions --}}
        {{ $slot }}

        {{-- Replies --}}
        @if($comment->replies->count() > 0 && $depth < config('sb-comments.max_depth', 3))
            <div class="mt-4 space-y-4">
                @foreach($comment->replies as $reply)
                    <x-sb-comment :comment="$reply" :depth="$depth + 1" />
                @endforeach
            </div>
        @endif
    </div>
</div>
