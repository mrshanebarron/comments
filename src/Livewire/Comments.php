<?php

namespace MrShaneBarron\Comments\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use MrShaneBarron\Comments\Models\Comment;

class Comments extends Component
{
    use WithPagination;

    public string $commentableType;
    public int $commentableId;
    public string $body = '';
    public ?int $replyingTo = null;
    public ?int $editing = null;
    public string $editBody = '';
    public string $sort = 'newest';
    public ?string $guestName = null;

    protected $listeners = ['commentAdded' => '$refresh'];

    public function mount(string $commentableType, int $commentableId): void
    {
        $this->commentableType = $commentableType;
        $this->commentableId = $commentableId;
        $this->sort = config('ld-comments.default_sort', 'newest');
    }

    public function addComment(): void
    {
        $this->validate([
            'body' => 'required|min:1',
            'guestName' => config('ld-comments.allow_guests') && config('ld-comments.guest_name_required') && !auth()->check()
                ? 'required|min:2'
                : 'nullable',
        ]);

        if (!config('ld-comments.allow_guests') && !auth()->check()) {
            return;
        }

        $modelClass = config('ld-comments.model', Comment::class);

        $modelClass::create([
            'commentable_type' => $this->commentableType,
            'commentable_id' => $this->commentableId,
            'user_id' => auth()->id(),
            'parent_id' => $this->replyingTo,
            'body' => $this->body,
            'guest_name' => $this->guestName,
            'approved' => !config('ld-comments.moderation', false),
        ]);

        $this->body = '';
        $this->replyingTo = null;
        $this->guestName = null;

        $this->dispatch('commentAdded');
    }

    public function reply(int $commentId): void
    {
        $this->replyingTo = $commentId;
        $this->editing = null;
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
        $this->body = '';
    }

    public function edit(int $commentId): void
    {
        $modelClass = config('ld-comments.model', Comment::class);
        $comment = $modelClass::find($commentId);

        if ($comment && $this->canEdit($comment)) {
            $this->editing = $commentId;
            $this->editBody = $comment->body;
            $this->replyingTo = null;
        }
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editBody' => 'required|min:1',
        ]);

        $modelClass = config('ld-comments.model', Comment::class);
        $comment = $modelClass::find($this->editing);

        if ($comment && $this->canEdit($comment)) {
            $comment->update(['body' => $this->editBody]);
        }

        $this->editing = null;
        $this->editBody = '';
    }

    public function cancelEdit(): void
    {
        $this->editing = null;
        $this->editBody = '';
    }

    public function delete(int $commentId): void
    {
        $modelClass = config('ld-comments.model', Comment::class);
        $comment = $modelClass::find($commentId);

        if ($comment && $this->canDelete($comment)) {
            if (config('ld-comments.soft_deletes', true)) {
                $comment->delete();
            } else {
                $comment->forceDelete();
            }
        }
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
        $this->resetPage();
    }

    protected function canEdit(Comment $comment): bool
    {
        if (!config('ld-comments.editable')) {
            return false;
        }

        if (!auth()->check()) {
            return false;
        }

        if ($comment->user_id !== auth()->id()) {
            return false;
        }

        return $comment->canEdit();
    }

    protected function canDelete(Comment $comment): bool
    {
        if (!config('ld-comments.deletable')) {
            return false;
        }

        if (!auth()->check()) {
            return false;
        }

        return $comment->user_id === auth()->id();
    }

    public function render()
    {
        $modelClass = config('ld-comments.model', Comment::class);

        $query = $modelClass::where('commentable_type', $this->commentableType)
            ->where('commentable_id', $this->commentableId)
            ->root()
            ->with(['user', 'allReplies.user']);

        if (config('ld-comments.moderation')) {
            $query->approved();
        }

        if ($this->sort === 'newest') {
            $query->newest();
        } elseif ($this->sort === 'oldest') {
            $query->oldest();
        }

        $comments = $query->paginate(config('ld-comments.per_page', 10));

        return view('ld-comments::livewire.comments', [
            'comments' => $comments,
        ]);
    }
}
