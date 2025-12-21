# Comments

A threaded comments component for Laravel applications. Display and manage user comments with replies, avatars, and timestamps. Works with Livewire and Vue 3.

## Installation

```bash
composer require mrshanebarron/comments
```

## Livewire Usage

### Basic Usage

```blade
<livewire:sb-comments
    :comments="$comments"
    :current-user="auth()->user()->name"
/>
```

### With Replies

```blade
<livewire:sb-comments
    :comments="$comments"
    :current-user="auth()->user()->name"
    :current-user-avatar="auth()->user()->avatar_url"
    :allow-replies="true"
/>
```

### Livewire Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `comments` | array | `[]` | Array of comment objects |
| `current-user` | string | `null` | Current user's name |
| `current-user-avatar` | string | `null` | Current user's avatar URL |
| `allow-replies` | boolean | `true` | Enable reply functionality |

## Vue 3 Usage

### Setup

```javascript
import { SbComments } from './vendor/sb-comments';
app.component('SbComments', SbComments);
```

### Basic Usage

```vue
<template>
  <SbComments
    :comments="comments"
    :current-user="currentUser.name"
    :current-user-avatar="currentUser.avatar"
    @add-comment="handleAddComment"
    @add-reply="handleAddReply"
  />
</template>

<script setup>
import { ref } from 'vue';

const currentUser = { name: 'John Doe', avatar: '/avatars/john.jpg' };
const comments = ref([
  {
    id: '1',
    author: 'Jane Smith',
    avatar: '/avatars/jane.jpg',
    content: 'Great article!',
    timestamp: new Date().toISOString(),
    replies: []
  }
]);

const handleAddComment = (comment) => {
  comments.value.push(comment);
};

const handleAddReply = ({ parentIndex, comment }) => {
  comments.value[parentIndex].replies.push(comment);
};
</script>
```

### Vue Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `comments` | Array | `[]` | Array of comment objects |
| `currentUser` | String | `null` | Current user's display name |
| `currentUserAvatar` | String | `null` | Current user's avatar URL |
| `allowReplies` | Boolean | `true` | Enable threaded replies |

### Events

| Event | Payload | Description |
|-------|---------|-------------|
| `add-comment` | `comment` | New comment submitted |
| `add-reply` | `{ parentIndex, comment }` | Reply submitted |

## Comment Object

```javascript
{
  id: 'unique-id',
  author: 'User Name',
  avatar: '/path/to/avatar.jpg',
  content: 'Comment text...',
  timestamp: '2024-01-15T10:30:00Z',
  replies: [/* nested comments */]
}
```

## Features

- **Threaded Replies**: Nested comment threads
- **Avatars**: User avatars with fallback initials
- **Timestamps**: Relative time display (just now, 5m ago, etc.)
- **Reply Toggle**: Click to reply to specific comments
- **Cancel Reply**: Easy cancel for reply mode

## Styling

Uses Tailwind CSS:
- Rounded avatars
- Indented reply threads
- Border separators
- Hover states

## Requirements

- PHP 8.1+
- Laravel 10, 11, or 12
- Tailwind CSS 3.x

## License

MIT License
