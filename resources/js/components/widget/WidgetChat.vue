<template>
  <div class="tenanta-widget-chat">
    <div class="messages-container" ref="messagesRef">
      <div v-for="msg in messages" :key="msg.id" :class="['message', msg.direction]">
        <div class="message-content">
          {{ msg.content }}
        </div>
        <div class="message-time">{{ formatTime(msg.created_at) }}</div>
      </div>
    </div>
    <div class="input-container">
      <input 
        type="text" 
        v-model="newMessage" 
        @keyup.enter="sendMessage" 
        placeholder="Escribe un mensaje..."
      >
      <button @click="sendMessage" :disabled="!newMessage.trim()">
        <svg viewBox="0 0 24 24" width="24" height="24">
          <path fill="currentColor" d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, nextTick, watch } from 'vue';

const props = defineProps({
  messages: {
    type: Array as () => any[],
    default: () => []
  }
});

const emit = defineEmits(['send']);

const newMessage = ref('');
const messagesRef = ref<HTMLElement | null>(null);

const sendMessage = () => {
  if (newMessage.value.trim()) {
    emit('send', newMessage.value);
    newMessage.value = '';
  }
};

const formatTime = (dateStr: string) => {
  if (!dateStr) return '';
  const date = new Date(dateStr);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

const scrollToBottom = async () => {
  await nextTick();
  if (messagesRef.value) {
    messagesRef.value.scrollTop = messagesRef.value.scrollHeight;
  }
};

onMounted(scrollToBottom);
watch(() => props.messages, scrollToBottom, { deep: true });
</script>

<style scoped>
.tenanta-widget-chat {
  display: flex;
  flex-direction: column;
  height: 400px;
}
.messages-container {
  flex: 1;
  padding: 15px;
  overflow-y: auto;
  background-color: #f5f5f5;
}
.message {
  margin-bottom: 10px;
  display: flex;
  flex-direction: column;
}
.message.inbound {
  align-items: flex-start;
}
.message.outbound {
  align-items: flex-end;
}
.message-content {
  max-width: 80%;
  padding: 8px 12px;
  border-radius: 12px;
  font-size: 14px;
}
.inbound .message-content {
  background-color: white;
  border: 1px solid #ddd;
}
.outbound .message-content {
  background-color: #1976D2;
  color: white;
}
.message-time {
  font-size: 10px;
  color: #999;
  margin-top: 2px;
}
.input-container {
  display: flex;
  padding: 10px;
  background-color: white;
  border-top: 1px solid #eee;
}
.input-container input {
  flex: 1;
  border: 1px solid #ddd;
  border-radius: 20px;
  padding: 8px 15px;
  outline: none;
}
.input-container button {
  background: none;
  border: none;
  color: #1976D2;
  margin-left: 5px;
  cursor: pointer;
}
.input-container button:disabled {
  color: #ccc;
}
</style>
