<div style="display: flex; gap: 16px; {{ $depth > 0 ? 'margin-left: 32px; padding-top: 16px; border-left: 2px solid #f3f4f6; padding-left: 16px;' : '' }}" wire:key="comment-{{ $comment->id }}">
    {{-- Avatar --}}
    <div style="flex-shrink: 0;">
        <div style="height: 40px; width: 40px; border-radius: 50%; background: #d1d5db; display: flex; align-items: center; justify-content: center;">
            <span style="color: #4b5563; font-weight: 500; font-size: 14px;">
                {{ strtoupper(substr($comment->user?->name ?? $comment->guest_name ?? 'G', 0, 1)) }}
            </span>
        </div>
    </div>

    <div style="flex: 1; min-width: 0;">
        {{-- Header --}}
        <div style="display: flex; align-items: center; gap: 8px; font-size: 14px;">
            <span style="font-weight: 500; color: #111827;">
                {{ $comment->user?->name ?? $comment->guest_name ?? 'Guest' }}
            </span>
            <span style="color: #6b7280;">
                {{ $comment->created_at->diffForHumans() }}
            </span>
            @if($comment->created_at != $comment->updated_at)
                <span style="color: #9ca3af; font-size: 12px;">(edited)</span>
            @endif
        </div>

        {{-- Body --}}
        @if($this->editing === $comment->id)
            <div style="margin-top: 8px; display: flex; flex-direction: column; gap: 8px;">
                <textarea
                    wire:model="editBody"
                    rows="3"
                    style="display: block; width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 8px 12px; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                ></textarea>
                <div style="display: flex; gap: 8px;">
                    <button
                        wire:click="saveEdit"
                        style="font-size: 14px; color: #4f46e5; background: none; border: none; cursor: pointer;"
                    >
                        Save
                    </button>
                    <button
                        wire:click="cancelEdit"
                        style="font-size: 14px; color: #6b7280; background: none; border: none; cursor: pointer;"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        @else
            <div style="margin-top: 4px; color: #374151; line-height: 1.6;">
                {!! nl2br(e($comment->body)) !!}
            </div>
        @endif

        {{-- Actions --}}
        <div style="margin-top: 8px; display: flex; align-items: center; gap: 16px; font-size: 14px;">
            @if($comment->canReply() && (auth()->check() || config('sb-comments.allow_guests')))
                <button
                    wire:click="reply({{ $comment->id }})"
                    style="color: #6b7280; background: none; border: none; cursor: pointer;"
                    onmouseover="this.style.color='#374151'"
                    onmouseout="this.style.color='#6b7280'"
                >
                    Reply
                </button>
            @endif

            @if(auth()->check() && auth()->id() === $comment->user_id)
                @if($comment->canEdit())
                    <button
                        wire:click="edit({{ $comment->id }})"
                        style="color: #6b7280; background: none; border: none; cursor: pointer;"
                        onmouseover="this.style.color='#374151'"
                        onmouseout="this.style.color='#6b7280'"
                    >
                        Edit
                    </button>
                @endif

                @if($comment->canDelete())
                    <button
                        wire:click="delete({{ $comment->id }})"
                        wire:confirm="Are you sure you want to delete this comment?"
                        style="color: #ef4444; background: none; border: none; cursor: pointer;"
                        onmouseover="this.style.color='#dc2626'"
                        onmouseout="this.style.color='#ef4444'"
                    >
                        Delete
                    </button>
                @endif
            @endif
        </div>

        {{-- Reply Form --}}
        @if($this->replyingTo === $comment->id)
            <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 8px;">
                <textarea
                    wire:model="body"
                    rows="2"
                    style="display: block; width: 100%; border-radius: 6px; border: 1px solid #d1d5db; padding: 8px 12px; font-size: 14px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);"
                    placeholder="Write a reply..."
                ></textarea>
                <div style="display: flex; gap: 8px;">
                    <button
                        wire:click="addComment"
                        style="display: inline-flex; align-items: center; padding: 6px 12px; border: none; font-size: 14px; font-weight: 500; border-radius: 6px; color: white; background: #4f46e5; cursor: pointer;"
                        onmouseover="this.style.background='#4338ca'"
                        onmouseout="this.style.background='#4f46e5'"
                    >
                        Reply
                    </button>
                    <button
                        wire:click="cancelReply"
                        style="font-size: 14px; color: #6b7280; background: none; border: none; cursor: pointer;"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        @endif

        {{-- Replies --}}
        @if($comment->replies->count() > 0 && $depth < config('sb-comments.max_depth', 3))
            <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 16px;">
                @foreach($comment->replies as $reply)
                    @include('sb-comments::livewire.partials.comment', ['comment' => $reply, 'depth' => $depth + 1])
                @endforeach
            </div>
        @endif
    </div>
</div>
