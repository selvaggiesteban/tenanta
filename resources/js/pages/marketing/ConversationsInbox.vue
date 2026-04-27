<template>
  <div class="d-flex flex-column h-100">
    <VContainer fluid class="pa-0 h-100 d-flex flex-column">
      <!-- Header de Omnicanalidad -->
      <VToolbar density="compact" border color="background">
        <VToolbarTitle class="font-weight-bold">
          Bandeja de Conversiones: {{ activeChannelLabel }}
        </VToolbarTitle>
        <VSpacer />
        <VBtn icon="mdi-sync" variant="text" size="small" @click="refreshConversations" :loading="store.loading" />
        <VBtn icon="mdi-cog-outline" variant="text" size="small" to="/settings" />
      </VToolbar>

      <div class="d-flex flex-grow-1 overflow-hidden" style="min-height: 0;">
        <!-- Lista de Conversaciones (Sidebar Izquierdo) -->
        <VNavigationDrawer permanent location="left" width="320" elevation="0" border>
          <div class="pa-4 border-bottom">
            <VTextField
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              placeholder="Buscar chat o cliente..."
              hide-details
              density="compact"
              variant="outlined"
            />
          </div>
          <VList lines="two" hover>
            <VListItem
              v-for="chat in filteredConversations"
              :key="chat.id"
              :active="store.activeConversation?.id === chat.id"
              @click="selectConversation(chat)"
              rounded="lg"
              class="mx-2 mb-1"
            >
              <template #prepend>
                <VAvatar color="grey-lighten-3">
                  <VImg v-if="chat.metadata?.avatar_url" :src="chat.metadata?.avatar_url" />
                  <span v-else>{{ getChatName(chat).charAt(0) }}</span>
                </VAvatar>
              </template>
              <VListItemTitle class="font-weight-bold">{{ getChatName(chat) }}</VListItemTitle>
              <VListItemSubtitle>
                <span v-if="store.typingAgents[chat.id]?.length" class="text-primary animate-pulse d-flex align-center">
                  <VIcon icon="mdi-dots-horizontal" size="x-small" class="mr-1" />
                  Escribiendo...
                </span>
                <span v-else>{{ chat.subject || 'Sin asunto' }}</span>
              </VListItemSubtitle>
              <template #append>
                <div class="d-flex flex-column align-end">
                  <span class="text-caption text-grey">{{ formatTime(chat.last_message_at) }}</span>
                  <VIcon 
                    :icon="getChannelIcon(chat.channel?.type)" 
                    size="small" 
                    :color="getChannelColor(chat.channel?.type)" 
                  />
                </div>
              </template>
            </VListItem>
            
            <VListItem v-if="filteredConversations.length === 0 && !store.loading" class="text-center py-8">
              <VListItemSubtitle>No se encontraron conversaciones</VListItemSubtitle>
            </VListItem>
          </VList>
        </VNavigationDrawer>

        <!-- Area de Chat (Centro) -->
        <div class="d-flex flex-column flex-grow-1 bg-grey-lighten-4">
          <template v-if="store.activeConversation">
            <div class="pa-4 border-bottom bg-surface d-flex align-center">
              <VAvatar size="40" class="mr-3" color="primary">
                <VImg v-if="store.activeConversation.metadata?.avatar_url" :src="store.activeConversation.metadata?.avatar_url" />
                <span v-else class="text-white">{{ getChatName(store.activeConversation).charAt(0) }}</span>
              </VAvatar>
              <div>
                <div class="font-weight-bold">{{ getChatName(store.activeConversation) }}</div>
                <div v-if="store.typingAgents[store.activeConversation.id]?.length" class="text-caption text-primary animate-pulse">
                  {{ store.typingAgents[store.activeConversation.id].join(', ') }} está escribiendo...
                </div>
                <div v-else class="text-caption text-success">
                  Online - vía {{ store.activeConversation.channel?.type }}
                </div>
              </div>
              <VSpacer />
              <VBtn
                color="success"
                variant="tonal"
                size="small"
                prepend-icon="mdi-phone"
                class="mr-2"
                @click="handleCall"
              >
                Llamar
              </VBtn>
              <VBtn 
                color="primary" 
                variant="tonal" 
                size="small" 
                prepend-icon="mdi-account-arrow-right"
                @click="assignDialog = true"
              >
                {{ store.activeConversation.assignee?.name || 'Asignar Agente' }}
              </VBtn>
            </div>

            <!-- Burbujas de Mensajes (Reales + Notas) -->
            <div class="flex-grow-1 overflow-y-auto pa-4 d-flex flex-column gap-4" ref="messageContainer">
              <div v-for="msg in store.messages" :key="msg.id" 
                :class="[
                  'd-flex', 
                  msg.type === 'note' ? 'justify-center' : (msg.direction === 'outbound' ? 'justify-end' : 'justify-start')
                ]"
              >
                <!-- Renderizado de NOTA PRIVADA -->
                <VCard
                  v-if="msg.type === 'note'"
                  color="warning-lighten-4"
                  class="pa-3 border-warning border-dashed"
                  max-width="85%"
                  elevation="0"
                  rounded="lg"
                >
                  <div class="d-flex align-center mb-1">
                    <VIcon icon="mdi-lock" size="x-small" color="warning" class="mr-1" />
                    <span class="text-caption font-weight-bold text-warning-darken-2 uppercase">Nota Interna</span>
                  </div>
                  <div class="text-body-2 italic text-grey-darken-3">{{ msg.content }}</div>
                  <div class="text-right text-caption mt-1 text-grey">
                    {{ msg.sender_name }} • {{ formatTime(msg.created_at) }}
                  </div>
                </VCard>

                <!-- Renderizado de MENSAJE ESTÁNDAR -->
                <VCard
                  v-else
                  :color="msg.direction === 'outbound' ? 'primary' : 'surface'"
                  :class="['pa-3', msg.direction === 'outbound' ? 'rounded-be-0' : 'rounded-bs-0']"
                  max-width="70%"
                  elevation="1"
                >
                  <div :class="msg.direction === 'outbound' ? 'text-white' : ''">{{ msg.content }}</div>
                  
                  <!-- Audio Player for recordings -->
                  <div v-if="msg.recording_url" class="mt-2">
                    <audio controls class="w-100" style="height: 32px;">
                      <source :src="msg.recording_url" type="audio/mpeg">
                      Tu navegador no soporta el elemento de audio.
                    </audio>
                  </div>

                  <div :class="['text-right text-caption mt-1', msg.direction === 'outbound' ? 'text-white-darken-1' : 'text-grey']">
                    {{ formatTime(msg.created_at) }}
                    <VIcon 
                      v-if="msg.direction === 'outbound'" 
                      :icon="getStatusIcon(msg.status)" 
                      size="x-small" 
                      class="ml-1" 
                    />
                  </div>
                </VCard>
              </div>
              
              <div v-if="store.messages.length === 0" class="text-center text-grey my-auto">
                No hay mensajes en esta conversación
              </div>
            </div>

            <!-- Input de Mensaje + Selector de Tipo -->
            <div class="pa-4 bg-surface border-top">
              <div class="d-flex align-center mb-2">
                <VTabs v-model="messageType" density="compact" hide-slider class="type-selector">
                  <VTab value="message" color="primary" variant="flat" rounded="pill" size="small" class="mr-2">
                    <VIcon icon="mdi-send" start size="x-small" /> Mensaje
                  </VTab>
                  <VTab value="note" color="warning" variant="flat" rounded="pill" size="small">
                    <VIcon icon="mdi-lock" start size="x-small" /> Nota Privada
                  </VTab>
                </VTabs>
                <VSpacer />
                <VBtn 
                  v-if="messageType === 'message'"
                  color="secondary" 
                  variant="tonal" 
                  size="small" 
                  prepend-icon="mdi-robot" 
                  class="mr-2"
                  :loading="suggestingAi"
                  @click="fetchAiSuggestion"
                >
                  IA Sugerencia
                </VBtn>
                <span v-if="messageType === 'note'" class="text-caption text-warning font-weight-bold">
                  Solo agentes verán esta nota
                </span>
              </div>

              <VMenu
                v-model="showCanned"
                :close-on-content-click="false"
                location="top start"
                max-height="300"
                width="300"
              >
                <template #activator="{ props }">
                  <VTextarea
                    v-bind="props"
                    v-model="newMessage"
                    :placeholder="messageType === 'note' ? 'Escribe una nota interna...' : 'Escribe un mensaje...'"
                    rows="2"
                    auto-grow
                    hide-details
                    variant="outlined"
                    density="compact"
                    :color="messageType === 'note' ? 'warning' : 'primary'"
                    @keyup.enter.exact.prevent="handleSendMessage"
                  />
                </template>

                <VList v-if="filteredCanned.length > 0">
                  <VListSubheader>RESPUESTAS RÁPIDAS</VListSubheader>
                  <VListItem
                    v-for="resp in filteredCanned"
                    :key="resp.id"
                    @click="insertCanned(resp)"
                  >
                    <template #prepend>
                      <VChip size="x-small" color="primary" class="mr-2">/{{ resp.shortcut }}</VChip>
                    </template>
                    <VListItemTitle class="text-truncate">{{ resp.content }}</VListItemTitle>
                  </VListItem>
                </VList>
                <VCard v-else class="pa-4 text-center text-caption text-grey">
                  No hay respuestas con ese atajo.
                </VCard>
              </VMenu>

              <div class="mt-2 d-flex gap-2">
                <VBtn icon="mdi-paperclip" size="x-small" variant="text" />
                <VBtn icon="mdi-emoticon-outline" size="x-small" variant="text" />
                <VBtn icon="mdi-flash-outline" size="x-small" variant="text" title="Respuestas rápidas" />
              </div>
            </div>
          </template>
          <div v-else class="h-100 d-flex flex-column align-center justify-center text-grey">
            <VIcon icon="mdi-chat-processing-outline" size="80" class="mb-4" />
            <p class="text-h6">Selecciona una conversación para empezar</p>
          </div>
        </div>

        <!-- Info del Lead/CRM (Sidebar Derecho) -->
        <VNavigationDrawer permanent location="right" width="280" border elevation="0" v-if="store.activeConversation">
          <div class="pa-4 text-center border-bottom">
            <VAvatar size="80" color="primary" class="mb-2">
              <span class="text-h4 text-white">{{ getChatName(store.activeConversation).charAt(0) }}</span>
            </VAvatar>
            <div class="text-h6 font-weight-bold">{{ getChatName(store.activeConversation) }}</div>
            <VBtn 
              v-if="store.activeConversation.contact_id" 
              size="x-small" 
              color="primary" 
              variant="text" 
              :to="`/crm/clients/${store.activeConversation.contact_id}`"
            >
              Ver ficha CRM
            </VBtn>
            <VBtn v-else size="x-small" color="primary" variant="text" @click="linkDialog = true">Vincular a CRM</VBtn>
          </div>
          <VList density="compact">
            <VListSubheader>DATOS DEL CONTACTO</VListSubheader>
            <VListItem 
              prepend-icon="mdi-email" 
              title="Email" 
              :subtitle="store.activeConversation.contact?.email || 'No disponible'" 
            />
            <VListItem 
              prepend-icon="mdi-phone" 
              title="Teléfono" 
              :subtitle="store.activeConversation.contact?.phone || store.activeConversation.external_id" 
            />
            <VListItem prepend-icon="mdi-tag-outline" title="Etiquetas">
              <div class="d-flex flex-wrap gap-1 mt-1">
                <VChip size="x-small" color="info">Lead</VChip>
                <VChip size="x-small" :color="getChannelColor(store.activeConversation.channel?.type)">
                  {{ store.activeConversation.channel?.type }}
                </VChip>
              </div>
            </VListItem>
          </VList>
        </VNavigationDrawer>
      </div>
    </VContainer>

    <!-- Vincular a CRM Dialog -->
    <VDialog v-model="linkDialog" max-width="500">
      <VCard>
        <VCardTitle>Vincular Conversación a Contacto</VCardTitle>
        <VCardText>
          <p class="text-body-2 mb-4">Busca un contacto existente en el CRM para asociar esta conversación.</p>
          <VTextField
            v-model="contactSearch"
            label="Buscar contacto..."
            prepend-inner-icon="mdi-magnify"
            hide-details
            variant="outlined"
            density="compact"
            class="mb-4"
            @update:model-value="searchContacts"
          />
          
          <VList v-if="contactResults.length > 0" lines="two" border rounded>
            <VListItem
              v-for="contact in contactResults"
              :key="contact.id"
              :title="contact.name"
              :subtitle="`${contact.email || ''} - ${contact.client?.name || ''}`"
              @click="linkToContact(contact)"
            >
              <template #append>
                <VIcon icon="mdi-link-plus" color="primary" />
              </template>
            </VListItem>
          </VList>
          
          <div v-else-if="searchingContacts" class="text-center py-4">
            <VProgressCircular indeterminate size="24" color="primary" />
          </div>
          
          <div v-else-if="contactSearch.length >= 2" class="text-center py-4 text-grey">
            No se encontraron contactos
          </div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="linkDialog = false">Cancelar</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Asignar Agente Dialog -->
    <VDialog v-model="assignDialog" max-width="500">
      <VCard>
        <VCardTitle>Asignar Agente a Conversación</VCardTitle>
        <VCardText>
          <VTextField
            v-model="agentSearch"
            label="Buscar agente por nombre..."
            prepend-inner-icon="mdi-account-search"
            hide-details
            variant="outlined"
            density="compact"
            class="mb-4"
            @update:model-value="searchAgents"
          />
          
          <VList v-if="agentResults.length > 0" lines="two" border rounded>
            <VListItem
              v-for="user in agentResults"
              :key="user.id"
              :title="user.name"
              :subtitle="user.role"
              @click="handleAssignAgent(user)"
            >
              <template #prepend>
                <VAvatar color="grey-lighten-3" size="32">
                  <VImg v-if="user.avatar_url" :src="user.avatar_url" />
                  <span v-else>{{ user.name.charAt(0) }}</span>
                </VAvatar>
              </template>
              <template #append>
                <VIcon icon="mdi-account-plus" color="primary" />
              </template>
            </VListItem>
          </VList>
          
          <div v-else-if="searchingAgents" class="text-center py-4">
            <VProgressCircular indeterminate size="24" color="primary" />
          </div>
          
          <div v-else-if="agentSearch.length >= 2" class="text-center py-4 text-grey">
            No se encontraron agentes
          </div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="assignDialog = false">Cancelar</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { useOmnichannelStore } from '@/stores/omnichannel'
import { useAuthStore } from '@/stores/auth'
import api from '@/api'
import type { Conversation } from '@/types/omnichannel'

const route = useRoute()
const store = useOmnichannelStore()
const authStore = useAuthStore()
const search = ref('')
const newMessage = ref('')
const messageType = ref<'message' | 'note'>('message')
const messageContainer = ref<HTMLElement | null>(null)
let typingTimeout: any = null

// Emit typing events when user types
watch(newMessage, (val) => {
  if (!store.activeConversation) return
  
  // Start typing
  if (val && !typingTimeout) {
    store.sendTypingEvent(store.activeConversation.id, true)
  }

  // Clear existing timeout
  if (typingTimeout) clearTimeout(typingTimeout)

  // Stop typing after 3s of inactivity
  typingTimeout = setTimeout(() => {
    if (store.activeConversation) {
      store.sendTypingEvent(store.activeConversation.id, false)
    }
    typingTimeout = null
  }, 3000)
})

// CRM Link State
const linkDialog = ref(false)
const contactSearch = ref('')
const contactResults = ref<any[]>([])
const searchingContacts = ref(false)

// Canned Responses State
const showCanned = ref(false)
const cannedSearch = ref('')

// Agent Assignment State
const assignDialog = ref(false)
const agentSearch = ref('')
const agentResults = ref<any[]>([])
const searchingAgents = ref(false)
const suggestingAi = ref(false)

async function fetchAiSuggestion() {
  if (!store.activeConversation) return
  
  suggestingAi.value = true
  try {
    const suggestion = await store.getAiSuggestion(store.activeConversation.id)
    newMessage.value = suggestion
  } catch (e) {
    console.error('Error obteniendo sugerencia:', e)
  } finally {
    suggestingAi.value = false
  }
}

async function handleCall() {
  if (!store.activeConversation) return
  
  const phoneNumber = store.activeConversation.contact?.phone || store.activeConversation.external_id
  if (!phoneNumber) {
    alert('No hay un número de teléfono asociado a esta conversación.')
    return
  }

  try {
    // Aquí se llamaría a la API de telefonía (Twilio, Voiso, etc)
    // Por ahora simulamos el inicio de llamada
    console.log(`Iniciando llamada a ${phoneNumber}...`)
    await api.post(`/omnichannel/conversations/${store.activeConversation.id}/call`)
    alert(`Llamando a ${phoneNumber}...`)
  } catch (e) {
    console.error('Error al iniciar la llamada:', e)
    alert('Error al intentar realizar la llamada.')
  }
}

async function searchAgents() {
  searchingAgents.value = true
  try {
    const response = await api.get('/auth/users', {
      params: { search: agentSearch.value }
    })
    agentResults.value = response.data.data
  } catch (e) {
    console.error('Error searching agents', e)
  } finally {
    searchingAgents.value = false
  }
}

async function handleAssignAgent(user: any) {
  if (!store.activeConversation) return
  
  try {
    await store.assignAgent(store.activeConversation.id, user.id)
    assignDialog.value = false
    agentSearch.value = ''
    agentResults.value = []
  } catch (e) {
    // Error handled by store
  }
}

watch(assignDialog, (val) => {
  if (val && agentResults.value.length === 0) {
    searchAgents()
  }
})

const filteredCanned = computed(() => {
  if (!cannedSearch.value) return store.cannedResponses
  return store.cannedResponses.filter(r => 
    r.shortcut.toLowerCase().includes(cannedSearch.value.toLowerCase()) ||
    r.content.toLowerCase().includes(cannedSearch.value.toLowerCase())
  )
})

function insertCanned(response: any) {
  // Replace the last "/" and search term with the content
  const lastSlashIndex = newMessage.value.lastIndexOf('/')
  if (lastSlashIndex !== -1) {
    newMessage.value = newMessage.value.substring(0, lastSlashIndex) + response.content
  } else {
    newMessage.value += response.content
  }
  showCanned.value = false
  cannedSearch.value = ''
}

// Watch for "/" to trigger canned responses
watch(newMessage, (val) => {
  const lastChar = val.slice(-1)
  if (lastChar === '/') {
    showCanned.value = true
    cannedSearch.value = ''
  } else if (showCanned.value) {
    // If menu is open, update search based on what's after the last "/"
    const lastSlashIndex = val.lastIndexOf('/')
    if (lastSlashIndex !== -1) {
      cannedSearch.value = val.substring(lastSlashIndex + 1)
    } else {
      showCanned.value = false
    }
  }
})

async function searchContacts() {
  if (contactSearch.value.length < 2) {
    contactResults.value = []
    return
  }
  
  searchingContacts.value = true
  try {
    const response = await api.get('/crm/contacts', {
      params: { search: contactSearch.value }
    })
    contactResults.value = response.data.data
  } catch (e) {
    console.error('Error searching contacts', e)
  } finally {
    searchingContacts.value = false
  }
}

async function linkToContact(contact: any) {
  if (!store.activeConversation) return
  
  try {
    await store.linkContact(store.activeConversation.id, contact.id)
    linkDialog.value = false
    contactSearch.value = ''
    contactResults.value = []
  } catch (e) {
    // Error handled by store
  }
}

const activeChannel = computed(() => {
  const path = route.path
  if (path.includes('email')) return 'email'
  if (path.includes('messenger')) return 'messenger'
  if (path.includes('instagram')) return 'instagram'
  if (path.includes('telegram')) return 'telegram'
  return '' // Mostrar todos si no hay filtro
})

const activeChannelLabel = computed(() => {
  const labels: any = { 
    email: 'Email (SMTP/G-Suite)', 
    messenger: 'FB Messenger', 
    instagram: 'Instagram Direct',
    telegram: 'Telegram',
    whatsapp: 'WhatsApp Oficial' 
  }
  return activeChannel.value ? labels[activeChannel.value] : 'Todos los canales'
})

const filteredConversations = computed(() => {
  return store.conversations.filter(c => {
    // Filtrar por canal basado en la ruta actual, si está vacío mostrar todos
    const typeMatch = activeChannel.value ? c.channel?.type.includes(activeChannel.value) : true
    
    // Filtrar por búsqueda
    const name = getChatName(c).toLowerCase()
    const searchMatch = name.includes(search.value.toLowerCase()) || 
                       c.external_id.includes(search.value)
    
    return typeMatch && searchMatch
  })
})

function getChatName(chat: Conversation): string {
  if (chat.contact?.name) return chat.contact.name
  if (chat.metadata?.sender_name) return chat.metadata.sender_name
  return chat.external_id
}

function getChannelIcon(type?: string) {
  const icons: any = { 
    whatsapp: 'mdi-whatsapp', 
    messenger: 'mdi-facebook-messenger', 
    instagram: 'mdi-instagram',
    telegram: 'mdi-send',
    email_smtp: 'mdi-email', 
    email_gmail: 'mdi-google' 
  }
  return icons[type || ''] || 'mdi-chat'
}

function getChannelColor(type?: string) {
  const colors: any = { 
    whatsapp: 'success', 
    messenger: 'indigo', 
    instagram: 'pink',
    telegram: 'light-blue',
    email_smtp: 'blue', 
    email_gmail: 'error' 
  }
  return colors[type || ''] || 'grey'
}

function getStatusIcon(status: string) {
  switch (status) {
    case 'sent': return 'mdi-check'
    case 'delivered': return 'mdi-check-all'
    case 'read': return 'mdi-check-all' // Sería azul si Vuetify lo soporta así
    case 'failed': return 'mdi-alert-circle'
    default: return ''
  }
}

function formatTime(dateString: string | null): string {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
}

async function selectConversation(chat: Conversation) {
  store.activeConversation = chat
  await store.fetchMessages(chat.id)
  scrollToBottom()
}

async function handleSendMessage() {
  if (!newMessage.value || !store.activeConversation) return
  
  try {
    await store.sendMessage({
      conversation_id: store.activeConversation.id,
      message: newMessage.value,
      type: messageType.value
    })
    newMessage.value = ''
    // Reset to message after sending a note (optional, but usually better UX)
    messageType.value = 'message'
    scrollToBottom()
  } catch (e) {
    // Error handled by store
  }
}

function refreshConversations() {
  store.fetchConversations()
}

function scrollToBottom() {
  nextTick(() => {
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight
    }
  })
}

onMounted(() => {
  refreshConversations()
  store.fetchCannedResponses()
  
  if (authStore.user?.tenant_id) {
    store.subscribeToMessages(authStore.user.tenant_id)
  }
})

onUnmounted(() => {
  if (authStore.user?.tenant_id) {
    store.unsubscribeFromMessages(authStore.user.tenant_id)
    
    // Stop typing if unmounting
    if (store.activeConversation) {
      store.sendTypingEvent(store.activeConversation.id, false)
    }
  }
})

// Scroll automático cuando llegan nuevos mensajes
watch(() => store.messages.length, () => {
  scrollToBottom()
})
</script>

<style scoped>
.gap-4 { gap: 16px; }
.gap-2 { gap: 8px; }
.gap-1 { gap: 4px; }
.border-bottom { border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity)); }
.border-dashed { border-style: dashed !important; border-width: 1px !important; }

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: .5; }
}

.type-selector :deep(.v-btn) {
  text-transform: none;
  letter-spacing: 0;
  font-weight: 600;
}

.italic { font-style: italic; }
.uppercase { text-transform: uppercase; }
</style>
