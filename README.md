# Laravel Design Comments

Threaded comments system for Laravel. Supports Livewire, Blade, and Vue 3.

## Installation

```bash
composer require mrshanebarron/comments
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=ld-comments-migrations
php artisan migrate
```

## Setup

Add the `HasComments` trait to any model you want to be commentable:

```php
use LaravelDesign\Comments\Traits\HasComments;

class Post extends Model
{
    use HasComments;
}
```

## Usage

### Livewire Component

```blade
<livewire:ld-comments
    :commentable-type="get_class($post)"
    :commentable-id="$post->id"
/>

{{-- Short form using model --}}
<livewire:ld-comments :model="$post" />
```

### Blade Component

```blade
<x-ld-comments :model="$post" />

<x-ld-comments
    :commentable-type="App\Models\Article::class"
    :commentable-id="$article->id"
/>
```

### Vue 3 Component

```vue
<script setup>
import { LdComments } from '@laraveldesign/comments'

const props = defineProps({
  post: Object
})
</script>

<template>
  <LdComments
    :commentable-type="'App\\Models\\Post'"
    :commentable-id="post.id"
    :user="currentUser"
  />
</template>
```

## Features

- **Threaded replies**: Nested comment threads with configurable max depth
- **Editing**: Users can edit their own comments (within time window)
- **Deleting**: Users can delete their own comments
- **Moderation**: Optional approval queue for new comments
- **Guest comments**: Allow anonymous comments
- **Sorting**: Sort by newest or oldest
- **Pagination**: Paginated comment list
- **Soft deletes**: Deleted comments are soft-deleted by default

## Model Methods

The `HasComments` trait adds these methods to your model:

```php
// Get all comments
$post->comments;

// Get root-level comments only
$post->rootComments;

// Get approved comments only
$post->approvedComments;

// Add a comment programmatically
$post->addComment('Great article!', $userId);

// Add a reply
$post->addComment('Thanks!', $userId, $parentCommentId);

// Add a guest comment
$post->addComment('Nice post', null, null, 'Anonymous');

// Get comment counts
$post->getCommentsCount();
$post->getApprovedCommentsCount();
```

## Comment Model

The `Comment` model provides:

```php
use LaravelDesign\Comments\Models\Comment;

// Relationships
$comment->user;        // Comment author
$comment->commentable; // Parent model (Post, Article, etc.)
$comment->parent;      // Parent comment (for replies)
$comment->replies;     // Direct replies
$comment->allReplies;  // All nested replies

// Scopes
Comment::approved()->get();  // Only approved
Comment::root()->get();      // Only root-level
Comment::newest()->get();    // Order by newest
Comment::oldest()->get();    // Order by oldest

// Properties
$comment->depth;       // Nesting level (0 for root)
$comment->canReply();  // Check if can reply (based on max depth)
$comment->canEdit();   // Check if can edit (based on time window)
$comment->canDelete(); // Check if can delete
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=ld-comments-config
```

### Configuration Options

```php
// config/ld-comments.php
return [
    // Database table name
    'table' => 'comments',

    // Comment model (extend for customization)
    'model' => \LaravelDesign\Comments\Models\Comment::class,

    // User model
    'user_model' => \App\Models\User::class,

    // Comments per page
    'per_page' => 10,

    // Default sort order
    'default_sort' => 'newest', // newest, oldest

    // Maximum reply depth (null for unlimited)
    'max_depth' => 3,

    // Allow guest comments
    'allow_guests' => false,

    // Require guest name
    'guest_name_required' => true,

    // Require moderation approval
    'moderation' => false,

    // Allow editing comments
    'editable' => true,

    // Edit time window in minutes (null for unlimited)
    'edit_window' => 30,

    // Allow deleting comments
    'deletable' => true,

    // Use soft deletes
    'soft_deletes' => true,
];
```

## Events

```php
// Listen for comment events
Event::listen('comment.created', function ($comment) {
    // Send notification
});

Event::listen('comment.updated', function ($comment) {
    // Log edit
});

Event::listen('comment.deleted', function ($comment) {
    // Handle deletion
});
```

## Extending the Comment Model

Create a custom comment model:

```php
namespace App\Models;

use LaravelDesign\Comments\Models\Comment as BaseComment;

class Comment extends BaseComment
{
    protected $appends = ['formatted_date'];

    public function getFormattedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
```

Update config:

```php
'model' => \App\Models\Comment::class,
```

## Customization

### Publishing Views

```bash
php artisan vendor:publish --tag=ld-comments-views
```

### Styling

The component uses Tailwind CSS classes. Customize by publishing views and modifying classes.

## Related Packages

- **mrshanebarron/likes** - Add reactions to comments
- **mrshanebarron/editor** - Rich text comment editing

## License

MIT
