<div style="display: flex; flex-direction: column; gap: 24px;">
    {{-- Comment Form --}}
    @if(auth()->check() || $this->allowGuests)
        <form wire:submit="addComment" style="display: flex; flex-direction: column; gap: 16px;">
            @if(!auth()->check() && $this->allowGuests)
                <div>
                    <label for="guest_name" style="display: block; font-size: 14px; font-weight: 500; color: #374151;">Name {{ $this->requireGuestName ? '*' : '(optional)' }}</label>
                    <input
                        type="text"
                        id="guest_name"
                        wire:model="guestName"
                        style="margin-top: 4px; display: block; width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 8px 12px; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                        placeholder="Your name"
                    >
                    @error('guestName') <span style="color: #ef4444; font-size: 14px;">{{ $message }}</span> @enderror
                </div>
            @endif

            <div>
                <textarea
                    wire:model="body"
                    rows="3"
                    style="display: block; width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 8px 12px; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); resize: vertical;"
                    placeholder="Write a comment..."
                ></textarea>
                @error('body') <span style="color: #ef4444; font-size: 14px;">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button
                    type="submit"
                    style="display: inline-flex; align-items: center; padding: 8px 16px; border: none; font-size: 14px; font-weight: 500; border-radius: 6px; color: white; background: #4f46e5; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                    onmouseover="this.style.background='#4338ca'"
                    onmouseout="this.style.background='#4f46e5'"
                >
                    Post Comment
                </button>
            </div>
        </form>
    @else
        <p style="color: #6b7280; font-size: 14px;">Please <a href="{{ route('login') }}" style="color: #4f46e5; text-decoration: none;" onmouseover="this.style.color='#4338ca'" onmouseout="this.style.color='#4f46e5'">log in</a> to comment.</p>
    @endif

    {{-- Sort Options --}}
    @if($comments->count() > 1)
        <div style="display: flex; align-items: center; gap: 16px; font-size: 14px;">
            <span style="color: #6b7280;">Sort by:</span>
            <button
                wire:click="setSort('newest')"
                style="font-weight: 500; border: none; background: transparent; cursor: pointer; {{ $this->sort === 'newest' ? 'color: #4f46e5;' : 'color: #4b5563;' }}"
            >
                Newest
            </button>
            <button
                wire:click="setSort('oldest')"
                style="font-weight: 500; border: none; background: transparent; cursor: pointer; {{ $this->sort === 'oldest' ? 'color: #4f46e5;' : 'color: #4b5563;' }}"
            >
                Oldest
            </button>
        </div>
    @endif

    {{-- Comments List --}}
    <div style="display: flex; flex-direction: column; gap: 16px;">
        @forelse($comments as $comment)
            @include('sb-comments::livewire.partials.comment', ['comment' => $comment, 'depth' => 0])
        @empty
            <p style="color: #6b7280; text-align: center; padding: 32px 0;">No comments yet. Be the first to comment!</p>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($comments->hasPages())
        <div style="margin-top: 16px;">
            {{ $comments->links() }}
        </div>
    @endif
</div>
