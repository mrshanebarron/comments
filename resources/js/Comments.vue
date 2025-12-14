<template>
  <div class="space-y-6">
    <div v-for="(comment, index) in comments" :key="comment.id || index" class="flex gap-4">
      <img v-if="comment.avatar" :src="comment.avatar" alt="" class="w-10 h-10 rounded-full flex-shrink-0">
      <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
        <span class="text-gray-500 text-sm font-medium">{{ (comment.author || 'A').charAt(0) }}</span>
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <span class="font-medium text-gray-900">{{ comment.author || 'Anonymous' }}</span>
          <span class="text-sm text-gray-500">{{ formatTime(comment.timestamp) }}</span>
        </div>
        <p class="text-gray-700">{{ comment.content }}</p>
        <button v-if="allowReplies" @click="startReply(index)" class="text-sm text-blue-600 hover:text-blue-800 mt-2">Reply</button>

        <div v-if="comment.replies?.length" class="mt-4 space-y-4 pl-4 border-l-2 border-gray-200">
          <div v-for="reply in comment.replies" :key="reply.id" class="flex gap-3">
            <img v-if="reply.avatar" :src="reply.avatar" alt="" class="w-8 h-8 rounded-full flex-shrink-0">
            <div v-else class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
              <span class="text-gray-500 text-xs font-medium">{{ (reply.author || 'A').charAt(0) }}</span>
            </div>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="font-medium text-gray-900 text-sm">{{ reply.author || 'Anonymous' }}</span>
                <span class="text-xs text-gray-500">{{ formatTime(reply.timestamp) }}</span>
              </div>
              <p class="text-gray-700 text-sm">{{ reply.content }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="currentUser" class="flex gap-4 pt-4 border-t border-gray-200">
      <img v-if="currentUserAvatar" :src="currentUserAvatar" alt="" class="w-10 h-10 rounded-full flex-shrink-0">
      <div v-else class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
        <span class="text-gray-500 text-sm font-medium">{{ currentUser.charAt(0) }}</span>
      </div>
      <div class="flex-1">
        <div v-if="replyingTo !== null" class="text-sm text-gray-500 mb-2">
          Replying to {{ comments[replyingTo]?.author || 'comment' }}
          <button @click="cancelReply" class="text-blue-600 hover:text-blue-800 ml-2">Cancel</button>
        </div>
        <textarea
          v-model="newComment"
          rows="3"
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
          placeholder="Write a comment..."
        ></textarea>
        <button
          @click="addComment"
          class="mt-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          {{ replyingTo !== null ? 'Reply' : 'Comment' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';

export default {
  name: 'SbComments',
  props: {
    comments: { type: Array, default: () => [] },
    currentUser: { type: String, default: null },
    currentUserAvatar: { type: String, default: null },
    allowReplies: { type: Boolean, default: true }
  },
  emits: ['add-comment', 'add-reply'],
  setup(props, { emit }) {
    const newComment = ref('');
    const replyingTo = ref(null);

    const formatTime = (timestamp) => {
      if (!timestamp) return '';
      const date = new Date(timestamp);
      const now = new Date();
      const diff = Math.floor((now - date) / 1000);
      if (diff < 60) return 'just now';
      if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
      if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
      return `${Math.floor(diff / 86400)}d ago`;
    };

    const startReply = (index) => { replyingTo.value = index; };
    const cancelReply = () => { replyingTo.value = null; };

    const addComment = () => {
      if (!newComment.value.trim()) return;
      const comment = {
        id: Date.now().toString(),
        author: props.currentUser || 'Anonymous',
        avatar: props.currentUserAvatar,
        content: newComment.value,
        timestamp: new Date().toISOString(),
        replies: []
      };
      if (replyingTo.value !== null) {
        emit('add-reply', { parentIndex: replyingTo.value, comment });
      } else {
        emit('add-comment', comment);
      }
      newComment.value = '';
      replyingTo.value = null;
    };

    return { newComment, replyingTo, formatTime, startReply, cancelReply, addComment };
  }
};
</script>
