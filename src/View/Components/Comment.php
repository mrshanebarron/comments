<?php

namespace MrShaneBarron\Comments\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use MrShaneBarron\Comments\Models\Comment as CommentModel;

class Comment extends Component
{
    public function __construct(
        public CommentModel $comment,
        public int $depth = 0
    ) {}

    public function render(): View
    {
        return view('ld-comments::components.comment');
    }
}
