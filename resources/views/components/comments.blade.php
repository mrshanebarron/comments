<div class="ld-comments space-y-6">
    {{-- Comment Form --}}
    @if(auth()->check() || config('ld-comments.allow_guests'))
        {{ $form ?? '' }}
    @else
        <p class="text-gray-500 text-sm">Please <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500">log in</a> to comment.</p>
    @endif

    {{-- Comments List --}}
    <div class="space-y-4">
        @forelse($comments as $comment)
            <x-ld-comment :comment="$comment" />
        @empty
            <p class="text-gray-500 text-center py-8">No comments yet. Be the first to comment!</p>
        @endforelse
    </div>
</div>
