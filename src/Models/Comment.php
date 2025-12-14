<?php

namespace MrShaneBarron\Comments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('sb-comments.table', 'comments'));
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('sb-comments.user_model'));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id')->orderBy('created_at');
    }

    public function allReplies(): HasMany
    {
        return $this->replies()->with('allReplies');
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeNewest($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeOldest($query)
    {
        return $query->orderBy('created_at');
    }

    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;

        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth;
    }

    public function canReply(): bool
    {
        $maxDepth = config('sb-comments.max_depth');

        if ($maxDepth === null) {
            return true;
        }

        return $this->depth < $maxDepth;
    }

    public function canEdit(): bool
    {
        if (!config('sb-comments.editable')) {
            return false;
        }

        $editWindow = config('sb-comments.edit_window');

        if ($editWindow === null) {
            return true;
        }

        return $this->created_at->addMinutes($editWindow)->isFuture();
    }

    public function canDelete(): bool
    {
        return config('sb-comments.deletable', true);
    }
}
