<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const conversations = ref([
  { id: 1, name: 'Juan Pérez', platform: 'whatsapp', last_msg: 'Hola, necesito info del plan inicial', time: '10:30 AM', unread: 2 },
  { id: 2, name: 'María García', platform: 'messenger', last_msg: '¿Tienen soporte 24/7?', time: '09:15 AM', unread: 0 },
  { id: 3, name: 'Techno Store', platform: 'instagram', last_msg: 'Me encantó la landing SEO', time: 'Ayer', unread: 5 }
])

const activeChat = ref(conversations.value[0])
const newMessage = ref('')

const getPlatformIcon = (platform: string) => {
  return platform === 'whatsapp' ? 'mdi-whatsapp' : 
         platform === 'messenger' ? 'mdi-facebook-messenger' : 'mdi-instagram'
}

const getPlatformColor = (platform: string) => {
  return platform === 'whatsapp' ? '#25D366' : 
         platform === 'messenger' ? '#0084FF' : '#E4405F'
}

const sendMessage = () => {
  if (!newMessage.value) return
  console.log(`Enviando a ${activeChat.value.name} vía ${activeChat.value.platform}: ${newMessage.value}`)
  newMessage.value = ''
}
</script>

<template>
  <div class="omnicanal-inbox flex h-screen bg-white">
    <!-- Sidebar de Conversaciones -->
    <div class="w-1/3 border-r flex flex-col">
      <div class="p-4 bg-gray-50 border-b">
        <h2 class="text-xl font-bold text-indigo-600">Bandeja Unificada</h2>
        <div class="mt-2 flex space-x-2">
          <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">WhatsApp Conectado</span>
        </div>
      </div>
      <div class="flex-1 overflow-y-auto">
        <div v-for="chat in conversations" :key="chat.id" 
             @click="activeChat = chat"
             :class="{'bg-indigo-50 border-l-4 border-indigo-600': activeChat.id === chat.id}"
             class="p-4 border-b cursor-pointer hover:bg-gray-50 transition-colors">
          <div class="flex items-center">
            <div class="w-12 h-12 rounded-full bg-gray-200 mr-3 flex items-center justify-center font-bold text-gray-600 relative">
              {{ chat.name.charAt(0) }}
              <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white flex items-center justify-center"
                   :style="{ backgroundColor: getPlatformColor(chat.platform) }">
                <VIcon :icon="getPlatformIcon(chat.platform)" color="white" size="10" />
              </div>
            </div>
            <div class="flex-1 overflow-hidden">
              <div class="flex justify-between items-baseline">
                <h4 class="font-bold text-gray-900 truncate">{{ chat.name }}</h4>
                <span class="text-[10px] text-gray-400">{{ chat.time }}</span>
              </div>
              <p class="text-xs text-gray-500 truncate">{{ chat.last_msg }}</p>
            </div>
            <div v-if="chat.unread > 0" class="ml-2 bg-indigo-600 text-white text-[10px] rounded-full w-5 h-5 flex items-center justify-center">
              {{ chat.unread }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Área de Chat -->
    <div class="flex-1 flex flex-col bg-gray-100">
      <div class="p-4 bg-white border-b flex items-center shadow-sm">
        <h3 class="font-bold text-lg">{{ activeChat.name }}</h3>
        <span class="ml-3 px-2 py-0.5 rounded text-[10px] uppercase font-bold text-white"
              :style="{ backgroundColor: getPlatformColor(activeChat.platform) }">
          {{ activeChat.platform }}
        </span>
      </div>

      <div class="flex-1 p-6 overflow-y-auto space-y-4">
        <div class="flex justify-start">
          <div class="bg-white p-3 rounded-2xl rounded-tl-none shadow-sm max-w-md">
            <p class="text-sm text-gray-800">{{ activeChat.last_msg }}</p>
          </div>
        </div>
        <div class="flex justify-end">
          <div class="bg-indigo-600 text-white p-3 rounded-2xl rounded-tr-none shadow-sm max-w-md">
            <p class="text-sm">Hola! Soy el asistente de ventas de Tenanta. ¿Cómo podemos ayudarte hoy?</p>
          </div>
        </div>
      </div>

      <!-- Input de Mensaje -->
      <div class="p-4 bg-white border-t">
        <div class="flex space-x-2">
          <input v-model="newMessage" @keyup.enter="sendMessage"
                 type="text" placeholder="Escribe un mensaje..." 
                 class="flex-1 bg-gray-100 border-none rounded-full px-4 py-2 focus:ring-2 focus:ring-indigo-500 outline-none">
          <button @click="sendMessage" class="bg-indigo-600 text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-indigo-700 transition-colors">
            <VIcon icon="mdi-send" size="18" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
