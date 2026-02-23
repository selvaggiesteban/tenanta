<script setup lang="ts">
import { api } from '@/api'
import { useAuthStore } from '@/stores/auth'

interface Message {
  id?: number
  role: 'user' | 'assistant' | 'system' | 'tool'
  content: string
  tool_calls?: any[]
  tool_results?: any[]
  created_at?: string
}

interface Conversation {
  id: number
  title: string | null
  provider: string
  last_message_at: string | null
  messages?: Message[]
}

const authStore = useAuthStore()

const conversations = ref<Conversation[]>([])
const currentConversation = ref<Conversation | null>(null)
const messages = ref<Message[]>([])
const input = ref('')
const loading = ref(false)
const loadingConversations = ref(false)
const streamingContent = ref('')
const drawer = ref(true)

const welcomeMessage: Message = {
  role: 'assistant',
  content: '¡Hola! Soy tu asistente de IA para Tenanta CRM. Puedo ayudarte a:\n\n• Buscar clientes y leads\n• Consultar estadísticas del negocio\n• Crear y gestionar tareas\n• Buscar cotizaciones\n\n¿En qué puedo ayudarte hoy?',
}

onMounted(async () => {
  await loadConversations()
})

const loadConversations = async () => {
  loadingConversations.value = true
  try {
    const response = await api.get('/chat/conversations')
    conversations.value = response.data.data
  } catch (error) {
    console.error('Error loading conversations:', error)
  } finally {
    loadingConversations.value = false
  }
}

const createConversation = async () => {
  try {
    const response = await api.post('/chat/conversations')
    const conversation = response.data.data
    conversations.value.unshift(conversation)
    selectConversation(conversation)
  } catch (error) {
    console.error('Error creating conversation:', error)
  }
}

const selectConversation = async (conversation: Conversation) => {
  currentConversation.value = conversation
  loading.value = true

  try {
    const response = await api.get(`/chat/conversations/${conversation.id}`)
    const data = response.data.data
    messages.value = data.messages || []

    if (messages.value.length === 0) {
      messages.value = [welcomeMessage]
    }
  } catch (error) {
    console.error('Error loading conversation:', error)
    messages.value = [welcomeMessage]
  } finally {
    loading.value = false
  }
}

const deleteConversation = async (conversation: Conversation) => {
  try {
    await api.delete(`/chat/conversations/${conversation.id}`)
    conversations.value = conversations.value.filter(c => c.id !== conversation.id)

    if (currentConversation.value?.id === conversation.id) {
      currentConversation.value = null
      messages.value = [welcomeMessage]
    }
  } catch (error) {
    console.error('Error deleting conversation:', error)
  }
}

const startNewChat = () => {
  currentConversation.value = null
  messages.value = [welcomeMessage]
  input.value = ''
}

const sendMessage = async () => {
  if (!input.value.trim() || loading.value) return

  const userMessage = input.value.trim()
  input.value = ''

  // Add user message to UI
  messages.value.push({ role: 'user', content: userMessage })
  loading.value = true
  streamingContent.value = ''

  try {
    // Create conversation if needed
    if (!currentConversation.value) {
      const response = await api.post('/chat/conversations')
      currentConversation.value = response.data.data
      conversations.value.unshift(currentConversation.value!)
    }

    // Use streaming endpoint
    await streamResponse(currentConversation.value!.id, userMessage)
  } catch (error: any) {
    console.error('Error sending message:', error)
    messages.value.push({
      role: 'assistant',
      content: 'Lo siento, hubo un error al procesar tu mensaje. Por favor, intenta de nuevo.',
    })
  } finally {
    loading.value = false
    streamingContent.value = ''
  }
}

const streamResponse = async (conversationId: number, content: string) => {
  const token = authStore.token

  const response = await fetch(`/api/v1/chat/conversations/${conversationId}/stream`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${token}`,
      'Accept': 'text/event-stream',
    },
    body: JSON.stringify({ content }),
  })

  if (!response.ok) {
    throw new Error('Stream request failed')
  }

  const reader = response.body?.getReader()
  const decoder = new TextDecoder()
  let assistantContent = ''
  let toolResults: any[] = []

  if (!reader) return

  while (true) {
    const { done, value } = await reader.read()
    if (done) break

    const chunk = decoder.decode(value)
    const lines = chunk.split('\n')

    for (const line of lines) {
      if (line.startsWith('data: ')) {
        try {
          const data = JSON.parse(line.substring(6))

          switch (data.type) {
            case 'text':
              assistantContent += data.content
              streamingContent.value = assistantContent
              break

            case 'tool_start':
              // Show tool execution indicator
              break

            case 'tool_result':
              toolResults.push({
                tool: data.tool,
                result: data.result,
              })
              break

            case 'done':
              // Finalize message
              messages.value.push({
                role: 'assistant',
                content: assistantContent,
                tool_results: toolResults.length > 0 ? toolResults : undefined,
              })

              // Update conversation title if it was just created
              await loadConversations()
              break

            case 'error':
              messages.value.push({
                role: 'assistant',
                content: `Error: ${data.message}`,
              })
              break
          }
        } catch (e) {
          // Ignore parse errors for incomplete chunks
        }
      }
    }
  }
}

const formatTime = (dateString: string | null) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-AR', {
    day: '2-digit',
    month: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const displayContent = computed(() => {
  if (streamingContent.value) {
    return streamingContent.value
  }
  return ''
})
</script>

<template>
  <div class="chat-page d-flex" style="height: calc(100vh - 120px)">
    <!-- Conversations Sidebar -->
    <VNavigationDrawer
      v-model="drawer"
      :width="280"
      permanent
      class="border-e"
    >
      <div class="pa-4">
        <VBtn
          color="primary"
          block
          prepend-icon="mdi-plus"
          @click="startNewChat"
        >
          Nueva Conversación
        </VBtn>
      </div>

      <VDivider />

      <VList v-if="!loadingConversations" density="compact" nav>
        <VListItem
          v-for="conv in conversations"
          :key="conv.id"
          :active="currentConversation?.id === conv.id"
          @click="selectConversation(conv)"
        >
          <template #prepend>
            <VIcon icon="mdi-chat-outline" size="small" />
          </template>
          <VListItemTitle class="text-truncate">
            {{ conv.title || 'Nueva conversación' }}
          </VListItemTitle>
          <VListItemSubtitle class="text-caption">
            {{ formatTime(conv.last_message_at) }}
          </VListItemSubtitle>
          <template #append>
            <VBtn
              icon
              variant="text"
              size="x-small"
              @click.stop="deleteConversation(conv)"
            >
              <VIcon icon="mdi-delete-outline" size="small" />
            </VBtn>
          </template>
        </VListItem>
      </VList>

      <div v-else class="pa-4 text-center">
        <VProgressCircular indeterminate size="24" />
      </div>
    </VNavigationDrawer>

    <!-- Chat Area -->
    <div class="flex-grow-1 d-flex flex-column">
      <!-- Header -->
      <VToolbar flat density="compact" class="border-b">
        <VBtn
          icon
          variant="text"
          class="d-md-none"
          @click="drawer = !drawer"
        >
          <VIcon icon="mdi-menu" />
        </VBtn>
        <VToolbarTitle>
          {{ currentConversation?.title || 'Chat AI' }}
        </VToolbarTitle>
        <VSpacer />
        <VChip size="small" variant="outlined">
          <VIcon icon="mdi-robot" size="small" class="me-1" />
          Claude
        </VChip>
      </VToolbar>

      <!-- Messages -->
      <div class="flex-grow-1 overflow-y-auto pa-4" ref="messagesContainer">
        <div class="messages-list">
          <div
            v-for="(message, i) in messages"
            :key="i"
            class="message-wrapper mb-4"
            :class="message.role"
          >
            <div class="d-flex" :class="{ 'justify-end': message.role === 'user' }">
              <VAvatar
                v-if="message.role === 'assistant'"
                size="32"
                color="primary"
                class="me-3"
              >
                <VIcon icon="mdi-robot" size="20" />
              </VAvatar>

              <VCard
                :color="message.role === 'user' ? 'primary' : 'surface-variant'"
                :class="message.role === 'user' ? 'text-white' : ''"
                max-width="70%"
                flat
              >
                <VCardText class="pa-3" style="white-space: pre-wrap">
                  {{ message.content }}
                </VCardText>

                <!-- Tool Results -->
                <template v-if="message.tool_results?.length">
                  <VDivider />
                  <VCardText class="pa-2">
                    <div class="text-caption text-medium-emphasis mb-1">
                      <VIcon icon="mdi-tools" size="x-small" class="me-1" />
                      Herramientas utilizadas:
                    </div>
                    <VChip
                      v-for="(tr, ti) in message.tool_results"
                      :key="ti"
                      size="x-small"
                      class="me-1 mb-1"
                    >
                      {{ tr.tool }}
                    </VChip>
                  </VCardText>
                </template>
              </VCard>

              <VAvatar
                v-if="message.role === 'user'"
                size="32"
                color="secondary"
                class="ms-3"
              >
                <VIcon icon="mdi-account" size="20" />
              </VAvatar>
            </div>
          </div>

          <!-- Streaming Content -->
          <div v-if="streamingContent" class="message-wrapper assistant mb-4">
            <div class="d-flex">
              <VAvatar size="32" color="primary" class="me-3">
                <VIcon icon="mdi-robot" size="20" />
              </VAvatar>
              <VCard color="surface-variant" max-width="70%" flat>
                <VCardText class="pa-3" style="white-space: pre-wrap">
                  {{ streamingContent }}
                  <span class="typing-cursor">|</span>
                </VCardText>
              </VCard>
            </div>
          </div>

          <!-- Loading Indicator -->
          <div v-if="loading && !streamingContent" class="message-wrapper assistant mb-4">
            <div class="d-flex">
              <VAvatar size="32" color="primary" class="me-3">
                <VIcon icon="mdi-robot" size="20" />
              </VAvatar>
              <VCard color="surface-variant" flat>
                <VCardText class="pa-3 d-flex align-center">
                  <VProgressCircular indeterminate size="16" width="2" class="me-2" />
                  Pensando...
                </VCardText>
              </VCard>
            </div>
          </div>
        </div>
      </div>

      <!-- Input -->
      <div class="pa-4 border-t">
        <VTextField
          v-model="input"
          placeholder="Escribe tu mensaje..."
          variant="outlined"
          hide-details
          :disabled="loading"
          autofocus
          @keyup.enter="sendMessage"
        >
          <template #append-inner>
            <VBtn
              icon
              variant="text"
              :loading="loading"
              :disabled="!input.trim()"
              @click="sendMessage"
            >
              <VIcon icon="mdi-send" />
            </VBtn>
          </template>
        </VTextField>
        <p class="text-caption text-medium-emphasis mt-2 mb-0 text-center">
          El asistente puede cometer errores. Verifica la información importante.
        </p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.typing-cursor {
  animation: blink 1s infinite;
}

@keyframes blink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
}

.messages-list {
  max-width: 900px;
  margin: 0 auto;
}
</style>
