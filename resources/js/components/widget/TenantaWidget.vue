<template>
  <div class="tenanta-widget-container" :class="{ 'is-open': isOpen }">
    <!-- Floating Button -->
    <div class="widget-button" @click="toggleWidget">
      <svg v-if="!isOpen" viewBox="0 0 24 24" width="32" height="32">
        <path fill="currentColor" d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" />
      </svg>
      <svg v-else viewBox="0 0 24 24" width="32" height="32">
        <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" />
      </svg>
    </div>

    <!-- Widget Window -->
    <div v-if="isOpen" class="widget-window">
      <div class="widget-header">
        <span>{{ tenantName }}</span>
        <button @click="toggleWidget">&times;</button>
      </div>

      <WidgetForm v-if="step === 'form'" @submitted="handleFormSubmit" />
      <WidgetChat v-else-if="step === 'chat'" :messages="messages" @send="handleSendMessage" />
      
      <div v-if="loading" class="loading-overlay">
        Cargando...
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, defineProps } from 'vue';
import WidgetForm from './WidgetForm.vue';
import WidgetChat from './WidgetChat.vue';
import axios from 'axios';

const props = defineProps({
  tenantId: {
    type: String,
    required: true
  },
  tenantName: {
    type: String,
    default: 'Soporte'
  },
  apiUrl: {
    type: String,
    default: '/api'
  }
});

const isOpen = ref(false);
const step = ref('form'); // form | chat
const messages = ref<any[]>([]);
const loading = ref(false);
const conversationId = ref<string | null>(null);
const token = ref<string | null>(localStorage.getItem(`tenanta_widget_token_${props.tenantId}`));

const toggleWidget = () => {
  isOpen.value = !isOpen.value;
};

const handleFormSubmit = async (formData: any) => {
  loading.value = true;
  try {
    const response = await axios.post(`${props.apiUrl}/widget/init`, {
      tenant_id: props.tenantId,
      ...formData
    });
    
    token.value = response.data.token;
    localStorage.setItem(`tenanta_widget_token_${props.tenantId}`, token.value!);
    conversationId.value = response.data.conversation.id;
    messages.value = response.data.messages || [];
    step.value = 'chat';
    
    setupEcho();
  } catch (error) {
    console.error('Error initializing chat:', error);
    alert('No se pudo iniciar el chat. Intenta de nuevo.');
  } finally {
    loading.value = false;
  }
};

const handleSendMessage = async (content: string) => {
  try {
    const response = await axios.post(`${props.apiUrl}/widget/message`, 
      { content, conversation_id: conversationId.value },
      { headers: { Authorization: `Bearer ${token.value}` } }
    );
    // Optimistic update or wait for Echo
    // messages.value.push(response.data.message);
  } catch (error) {
    console.error('Error sending message:', error);
  }
};

const setupEcho = () => {
  // Echo setup would go here. 
  // For custom element, we might need to load Echo dynamically or inject it.
  if ((window as any).Echo && conversationId.value) {
    (window as any).Echo.channel(`conversation.${conversationId.value}`)
      .listen('.MessageCreated', (e: any) => {
        messages.value.push(e.message);
      });
  }
};

onMounted(async () => {
  if (token.value) {
    // Try to restore session
    loading.value = true;
    try {
      const response = await axios.get(`${props.apiUrl}/widget/session`, {
        headers: { Authorization: `Bearer ${token.value}` }
      });
      conversationId.value = response.data.conversation.id;
      messages.value = response.data.messages;
      step.value = 'chat';
      setupEcho();
    } catch (e) {
      localStorage.removeItem(`tenanta_widget_token_${props.tenantId}`);
      token.value = null;
    } finally {
      loading.value = false;
    }
  }
});
</script>

<style scoped>
.tenanta-widget-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
  font-family: sans-serif;
}
.widget-button {
  width: 60px;
  height: 60px;
  background-color: #1976D2;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: transform 0.3s;
}
.widget-button:hover {
  transform: scale(1.1);
}
.widget-window {
  position: absolute;
  bottom: 80px;
  right: 0;
  width: 350px;
  max-height: 500px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.widget-header {
  background-color: #1976D2;
  color: white;
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.widget-header span {
  font-weight: bold;
}
.widget-header button {
  background: none;
  border: none;
  color: white;
  font-size: 24px;
  cursor: pointer;
}
.loading-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255,255,255,0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
}
</style>
