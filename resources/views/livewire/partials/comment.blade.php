<div class="flex gap-4 {{ $depth > 0 ? 'ml-8 pt-4 border-l-2 border-gray-100 pl-4' : '' }}" wire:key="comment-{{ $comment->id }}">
    {{-- Avatar --}}
    <div class="shrink-0">
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
        @if($this->editing === $comment->id)
            <div class="mt-2 flex flex-col gap-2">
                <textarea
                    wire:model="editBody"
                    rows="3"
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                ></textarea>
                <div class="flex gap-2">
                    <button
                        wire:click="saveEdit"
                        class="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        Save
                    </button>
                    <button
                        wire:click="cancelEdit"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        @else
            <div class="mt-1 text-gray-700 leading-relaxed">
                {!! nl2br(e($comment->body)) !!}
            </div>
        @endif

        {{-- Actions --}}
        <div class="mt-2 flex items-center gap-4 text-sm">
            @if($comment->canReply() && (auth()->check() || config('sb-comments.allow_guests')))
                <button
                    wire:click="reply({{ $comment->id }})"
                    class="text-gray-500 hover:text-gray-700"
                >
                    Reply
                </button>
            @endif

            @if(auth()->check() && auth()->id() === $comment->user_id)
                @if($comment->canEdit())
                    <button
                        wire:click="edit({{ $comment->id }})"
                        class="text-gray-500 hover:text-gray-700"
                    >
                        Edit
                    </button>
                @endif

                @if($comment->canDelete())
                    <button
                        wire:click="delete({{ $comment->id }})"
                        wire:confirm="Are you sure you want to delete this comment?"
                        class="text-red-500 hover:text-red-600"
                    >
                        Delete
                    </button>
                @endif
            @endif
        </div>

        {{-- Reply Form --}}
        @if($this->replyingTo === $comment->id)
            <div class="mt-4 flex flex-col gap-2">
                <textarea
                    wire:model="body"
                    rows="2"
                    class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Write a reply..."
                ></textarea>
                <div class="flex gap-2">
                    <button
                        wire:click="addComment"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                        Reply
                    </button>
                    <button
                        wire:click="cancelReply"
                        class="text-sm text-gray-500 hover:text-gray-700"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        {{-- Replies --}}
        @if($comment->replies->count() > 0 && $depth < config('sb-comments.max_depth', 3))
            <div class="mt-4 flex flex-col gap-4">
                @foreach($comment->replies as $reply)
                    @include('sb-comments::livewire.partials.comment', ['comment' => $reply, 'depth' => $depth + 1])
                @endforeach
            </div>
        @endif
    </div>
</div>
