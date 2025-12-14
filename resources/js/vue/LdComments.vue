<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface User {
  id: number
  name: string
  avatar?: string
}

interface Comment {
  id: number
  body: string
  user?: User | null
  guest_name?: string | null
  parent_id?: number | null
  created_at: string
  updated_at: string
  replies?: Comment[]
}

interface Props {
  commentableType: string
  commentableId: number
  comments?: Comment[]
  currentUser?: User | null
  allowGuests?: boolean
  maxDepth?: number
  apiEndpoint?: string
}

const props = withDefaults(defineProps<Props>(), {
  comments: () => [],
  currentUser: null,
  allowGuests: false,
  maxDepth: 3,
  apiEndpoint: '/api/comments',
})

const emit = defineEmits<{
  'comment-added': [comment: Comment]
  'comment-updated': [comment: Comment]
  'comment-deleted': [id: number]
}>()

const localComments = ref<Comment[]>(props.comments)
const newComment = ref('')
const guestName = ref('')
const replyingTo = ref<number | null>(null)
const replyBody = ref('')
const editingId = ref<number | null>(null)
const editBody = ref('')
const loading = ref(false)
const sort = ref<'newest' | 'oldest'>('newest')

const sortedComments = computed(() => {
  const rootComments = localComments.value.filter(c => !c.parent_id)
  return sort.value === 'newest'
    ? rootComments.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
    : rootComments.sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime())
})

const canComment = computed(() => props.currentUser || props.allowGuests)

async function submitComment() {
  if (!newComment.value.trim()) return
  if (!props.currentUser && props.allowGuests && !guestName.value.trim()) return

  loading.value = true

  try {
    const response = await fetch(props.apiEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        commentable_type: props.commentableType,
        commentable_id: props.commentableId,
        body: newComment.value,
        guest_name: guestName.value || undefined,
      }),
    })

    if (response.ok) {
      const comment = await response.json()
      localComments.value.push(comment)
      newComment.value = ''
      guestName.value = ''
      emit('comment-added', comment)
    }
  } finally {
    loading.value = false
  }
}

async function submitReply(parentId: number) {
  if (!replyBody.value.trim()) return

  loading.value = true

  try {
    const response = await fetch(props.apiEndpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        commentable_type: props.commentableType,
        commentable_id: props.commentableId,
        body: replyBody.value,
        parent_id: parentId,
        guest_name: guestName.value || undefined,
      }),
    })

    if (response.ok) {
      const comment = await response.json()
      const parent = findComment(localComments.value, parentId)
      if (parent) {
        parent.replies = parent.replies || []
        parent.replies.push(comment)
      }
      replyBody.value = ''
      replyingTo.value = null
      emit('comment-added', comment)
    }
  } finally {
    loading.value = false
  }
}

async function saveEdit(commentId: number) {
  if (!editBody.value.trim()) return

  loading.value = true

  try {
    const response = await fetch(`${props.apiEndpoint}/${commentId}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ body: editBody.value }),
    })

    if (response.ok) {
      const updated = await response.json()
      const comment = findComment(localComments.value, commentId)
      if (comment) {
        comment.body = updated.body
        comment.updated_at = updated.updated_at
      }
      editingId.value = null
      editBody.value = ''
      emit('comment-updated', updated)
    }
  } finally {
    loading.value = false
  }
}

async function deleteComment(commentId: number) {
  if (!confirm('Are you sure you want to delete this comment?')) return

  loading.value = true

  try {
    const response = await fetch(`${props.apiEndpoint}/${commentId}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
      },
    })

    if (response.ok) {
      removeComment(localComments.value, commentId)
      emit('comment-deleted', commentId)
    }
  } finally {
    loading.value = false
  }
}

function findComment(comments: Comment[], id: number): Comment | null {
  for (const comment of comments) {
    if (comment.id === id) return comment
    if (comment.replies) {
      const found = findComment(comment.replies, id)
      if (found) return found
    }
  }
  return null
}

function removeComment(comments: Comment[], id: number): boolean {
  const index = comments.findIndex(c => c.id === id)
  if (index !== -1) {
    comments.splice(index, 1)
    return true
  }
  for (const comment of comments) {
    if (comment.replies && removeComment(comment.replies, id)) {
      return true
    }
  }
  return false
}

function startReply(commentId: number) {
  replyingTo.value = commentId
  editingId.value = null
}

function startEdit(comment: Comment) {
  editingId.value = comment.id
  editBody.value = comment.body
  replyingTo.value = null
}

function cancelReply() {
  replyingTo.value = null
  replyBody.value = ''
}

function cancelEdit() {
  editingId.value = null
  editBody.value = ''
}

function getInitial(comment: Comment): string {
  const name = comment.user?.name || comment.guest_name || 'G'
  return name.charAt(0).toUpperCase()
}

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(diff / 3600000)
  const days = Math.floor(diff / 86400000)

  if (minutes < 1) return 'just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  return date.toLocaleDateString()
}

function canEditComment(comment: Comment): boolean {
  return props.currentUser?.id === comment.user?.id
}

function canDeleteComment(comment: Comment): boolean {
  return props.currentUser?.id === comment.user?.id
}

function getDepth(comment: Comment, depth: number = 0): number {
  return depth
}
</script>

<template>
  <div class="ld-comments space-y-6">
    <!-- Comment Form -->
    <div v-if="canComment" class="space-y-4">
      <div v-if="!currentUser && allowGuests">
        <label for="guest_name" class="block text-sm font-medium text-gray-700">Name</label>
        <input
          id="guest_name"
          v-model="guestName"
          type="text"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
          placeholder="Your name"
        >
      </div>
      <textarea
        v-model="newComment"
        rows="3"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        placeholder="Write a comment..."
      ></textarea>
      <div class="flex justify-end">
        <button
          @click="submitComment"
          :disabled="loading || !newComment.trim()"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
        >
          Post Comment
        </button>
      </div>
    </div>

    <!-- Sort Options -->
    <div v-if="sortedComments.length > 1" class="flex items-center gap-4 text-sm">
      <span class="text-gray-500">Sort by:</span>
      <button
        @click="sort = 'newest'"
        :class="['font-medium', sort === 'newest' ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900']"
      >
        Newest
      </button>
      <button
        @click="sort = 'oldest'"
        :class="['font-medium', sort === 'oldest' ? 'text-indigo-600' : 'text-gray-600 hover:text-gray-900']"
      >
        Oldest
      </button>
    </div>

    <!-- Comments List -->
    <div class="space-y-4">
      <template v-if="sortedComments.length > 0">
        <div v-for="comment in sortedComments" :key="comment.id">
          <slot name="comment" :comment="comment" :depth="0">
            <!-- Default comment template -->
            <div class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                  <span class="text-gray-600 font-medium text-sm">{{ getInitial(comment) }}</span>
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 text-sm">
                  <span class="font-medium text-gray-900">{{ comment.user?.name || comment.guest_name || 'Guest' }}</span>
                  <span class="text-gray-500">{{ formatDate(comment.created_at) }}</span>
                </div>
                <div v-if="editingId === comment.id" class="mt-2 space-y-2">
                  <textarea v-model="editBody" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                  <div class="flex gap-2">
                    <button @click="saveEdit(comment.id)" class="text-sm text-indigo-600 hover:text-indigo-500">Save</button>
                    <button @click="cancelEdit" class="text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                  </div>
                </div>
                <div v-else class="mt-1 text-gray-700 prose prose-sm max-w-none" v-html="comment.body.replace(/\n/g, '<br>')"></div>
                <div class="mt-2 flex items-center gap-4 text-sm">
                  <button v-if="canComment" @click="startReply(comment.id)" class="text-gray-500 hover:text-gray-700">Reply</button>
                  <button v-if="canEditComment(comment)" @click="startEdit(comment)" class="text-gray-500 hover:text-gray-700">Edit</button>
                  <button v-if="canDeleteComment(comment)" @click="deleteComment(comment.id)" class="text-red-500 hover:text-red-700">Delete</button>
                </div>
                <!-- Reply form -->
                <div v-if="replyingTo === comment.id" class="mt-4 space-y-2">
                  <textarea v-model="replyBody" rows="2" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Write a reply..."></textarea>
                  <div class="flex gap-2">
                    <button @click="submitReply(comment.id)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">Reply</button>
                    <button @click="cancelReply" class="text-sm text-gray-500 hover:text-gray-700">Cancel</button>
                  </div>
                </div>
              </div>
            </div>
          </slot>
        </div>
      </template>
      <p v-else class="text-gray-500 text-center py-8">No comments yet. Be the first to comment!</p>
    </div>
  </div>
</template>
