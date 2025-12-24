<?php

namespace MrShaneBarron\Comments\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use MrShaneBarron\Comments\Models\Comment;
use Illuminate\Support\Facades\Cache;

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
    public bool $useCache = true;
    public int $cacheTtl = 30; // minutes
    public bool $allowGuests = true;
    public bool $requireGuestName = false;

    protected $listeners = ['commentAdded' => '$refresh'];

    public function mount(
        string $commentableType,
        int $commentableId,
        bool $useCache = true,
        int $cacheTtl = 30,
        bool $allowGuests = true,
        bool $requireGuestName = false
    ): void {
        $this->commentableType = $commentableType;
        $this->commentableId = $commentableId;
        $this->sort = config('sb-comments.default_sort', 'newest');
        $this->useCache = $useCache;
        $this->cacheTtl = $cacheTtl;
        $this->allowGuests = $allowGuests;
        $this->requireGuestName = $requireGuestName;
    }

    protected function getCacheKey(): string
    {
        return "comments.{$this->commentableType}.{$this->commentableId}.{$this->sort}.page." . ($this->paginators['page'] ?? 1);
    }

    public function clearCache(): void
    {
        $pattern = "comments.{$this->commentableType}.{$this->commentableId}.*";
        Cache::forget($this->getCacheKey());

        // Clear all pages for this commentable
        for ($i = 1; $i <= 100; $i++) {
            foreach (['newest', 'oldest'] as $sort) {
                Cache::forget("comments.{$this->commentableType}.{$this->commentableId}.{$sort}.page.{$i}");
            }
        }
    }

    public static function clearCacheFor(string $commentableType, int $commentableId): void
    {
        for ($i = 1; $i <= 100; $i++) {
            foreach (['newest', 'oldest'] as $sort) {
                Cache::forget("comments.{$commentableType}.{$commentableId}.{$sort}.page.{$i}");
            }
        }
    }

    public function addComment(): void
    {
        $this->validate([
            'body' => 'required|min:1',
            'guestName' => $this->requireGuestName && !auth()->check()
                ? 'required|min:2'
                : 'nullable',
        ]);

        if (!$this->allowGuests && !auth()->check()) {
            return;
        }

        $modelClass = config('sb-comments.model', Comment::class);

        $modelClass::create([
            'commentable_type' => $this->commentableType,
            'commentable_id' => $this->commentableId,
            'user_id' => auth()->id(),
            'parent_id' => $this->replyingTo,
            'body' => $this->body,
            'guest_name' => $this->guestName,
            'approved' => !config('sb-comments.moderation', false),
        ]);

        $this->body = '';
        $this->replyingTo = null;
        $this->guestName = null;

        $this->clearCache();
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
        $modelClass = config('sb-comments.model', Comment::class);
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

        $modelClass = config('sb-comments.model', Comment::class);
        $comment = $modelClass::find($this->editing);

        if ($comment && $this->canEdit($comment)) {
            $comment->update(['body' => $this->editBody]);
            $this->clearCache();
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
        $modelClass = config('sb-comments.model', Comment::class);
        $comment = $modelClass::find($commentId);

        if ($comment && $this->canDelete($comment)) {
            if (config('sb-comments.soft_deletes', true)) {
                $comment->delete();
            } else {
                $comment->forceDelete();
            }
            $this->clearCache();
        }
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
        $this->resetPage();
    }

    protected function canEdit(Comment $comment): bool
    {
        if (!config('sb-comments.editable')) {
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
        if (!config('sb-comments.deletable')) {
            return false;
        }

        if (!auth()->check()) {
            return false;
        }

        return $comment->user_id === auth()->id();
    }

    public function render()
    {
        $comments = $this->getComments();

        return view('sb-comments::livewire.comments', [
            'comments' => $comments,
        ]);
    }

    protected function getComments()
    {
        if (!$this->useCache) {
            return $this->fetchComments();
        }

        return Cache::remember(
            $this->getCacheKey(),
            now()->addMinutes($this->cacheTtl),
            fn () => $this->fetchComments()
        );
    }

    protected function fetchComments()
    {
        $modelClass = config('sb-comments.model', Comment::class);

        $query = $modelClass::where('commentable_type', $this->commentableType)
            ->where('commentable_id', $this->commentableId)
            ->root()
            ->with(['user', 'allReplies.user']);

        if (config('sb-comments.moderation')) {
            $query->approved();
        }

        if ($this->sort === 'newest') {
            $query->newest();
        } elseif ($this->sort === 'oldest') {
            $query->oldest();
        }

        return $query->paginate(config('sb-comments.per_page', 10));
    }
}
