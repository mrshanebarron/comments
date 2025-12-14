<div class="ld-comments space-y-6">
    {{-- Comment Form --}}
    @if(auth()->check() || config('ld-comments.allow_guests'))
        <form wire:submit="addComment" class="space-y-4">
            @if(!auth()->check() && config('ld-comments.allow_guests'))
                <div>
                    <label for="guest_name" class="block text-sm font-medium text-gray-700">Name</label>
                    <input
                        type="text"
                        id="guest_name"
                        wire:model="guestName"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Your name"
                    >
                    @error('guestName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            @endif

            <div>
                <textarea
                    wire:model="body"
                    rows="3"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Write a comment..."
                ></textarea>
                @error('body') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Post Comment
                </button>
            </div>
        </form>
    @else
        <p class="text-gray-500 text-sm">Please <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500">log in</a> to comment.</p>
    @endif

    {{-- Sort Options --}}
    @if($comments->count() > 1)
        <div class="flex items-center gap-4 text-sm">
            <span class="text-gray-500">Sort by:</span>
            <button
                wire:click="setSort('newest')"
                @class(['font-medium', 'text-indigo-600' => $sort === 'newest', 'text-gray-600 hover:text-gray-900' => $sort !== 'newest'])
            >
                Newest
            </button>
            <button
                wire:click="setSort('oldest')"
                @class(['font-medium', 'text-indigo-600' => $sort === 'oldest', 'text-gray-600 hover:text-gray-900' => $sort !== 'oldest'])
            >
                Oldest
            </button>
        </div>
    @endif

    {{-- Comments List --}}
    <div class="space-y-4">
        @forelse($comments as $comment)
            @include('ld-comments::livewire.partials.comment', ['comment' => $comment, 'depth' => 0])
        @empty
            <p class="text-gray-500 text-center py-8">No comments yet. Be the first to comment!</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($comments->hasPages())
        <div class="mt-4">
            {{ $comments->links() }}
        </div>
    @endif
</div>
