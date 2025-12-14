<?php

namespace MrShaneBarron\Comments\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use MrShaneBarron\Comments\Models\Comment;

trait HasComments
{
    public function comments(): MorphMany
    {
        $model = config('sb-comments.model', Comment::class);

        return $this->morphMany($model, 'commentable');
    }

    public function rootComments(): MorphMany
    {
        return $this->comments()->root();
    }

    public function approvedComments(): MorphMany
    {
        return $this->comments()->approved();
    }

    public function addComment(string $body, ?int $userId = null, ?int $parentId = null, ?string $guestName = null): Comment
    {
        $model = config('sb-comments.model', Comment::class);

        return $this->comments()->create([
            'body' => $body,
            'user_id' => $userId,
            'parent_id' => $parentId,
            'guest_name' => $guestName,
            'approved' => !config('sb-comments.moderation', false),
        ]);
    }

    public function getCommentsCount(): int
    {
        return $this->comments()->count();
    }

    public function getApprovedCommentsCount(): int
    {
        return $this->approvedComments()->count();
    }
}
