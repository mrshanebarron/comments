<?php

namespace MrShaneBarron\Comments\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CommentForm extends Component
{
    public function __construct(
        public string $action,
        public ?int $parentId = null,
        public string $submitText = 'Post Comment',
        public ?string $placeholder = null
    ) {
        $this->placeholder = $placeholder ?? ($parentId ? 'Write a reply...' : 'Write a comment...');
    }

    public function render(): View
    {
        return view('ld-comments::components.comment-form');
    }
}
