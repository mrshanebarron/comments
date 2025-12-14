<?php

namespace MrShaneBarron\Comments\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use MrShaneBarron\Comments\Models\Comment;

class Comments extends Component
{
    public string $commentableType;
    public int $commentableId;
    public $comments;

    public function __construct(
        Model $model,
        public string $sort = 'newest'
    ) {
        $this->commentableType = get_class($model);
        $this->commentableId = $model->getKey();

        $modelClass = config('ld-comments.model', Comment::class);

        $query = $modelClass::where('commentable_type', $this->commentableType)
            ->where('commentable_id', $this->commentableId)
            ->root()
            ->with(['user', 'allReplies.user']);

        if (config('ld-comments.moderation')) {
            $query->approved();
        }

        if ($sort === 'newest') {
            $query->newest();
        } elseif ($sort === 'oldest') {
            $query->oldest();
        }

        $this->comments = $query->get();
    }

    public function render(): View
    {
        return view('ld-comments::components.comments');
    }
}
