<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useOmnichannelStore } from '@/stores/omnichannel'
import type { Channel, ChannelType } from '@/types/omnichannel'

const store = useOmnichannelStore()
const dialog = ref(false)
const deleteDialog = ref(false)
const channelToDelete = ref<Channel | null>(null)
const editingChannel = ref<Partial<Channel> | null>(null)

const channelTypes = [
  { title: 'WhatsApp Cloud API', value: 'whatsapp', icon: 'mdi-whatsapp', color: 'success' },
  { title: 'Facebook Messenger', value: 'messenger', icon: 'mdi-facebook-messenger', color: 'indigo' },
  { title: 'Instagram Direct', value: 'instagram', icon: 'mdi-instagram', color: 'pink' },
  { title: 'Telegram Bot API', value: 'telegram', icon: 'mdi-send', color: 'light-blue' },
  { title: 'Email (SMTP)', value: 'email_smtp', icon: 'mdi-email', color: 'blue' },
  { title: 'Gmail (OAuth)', value: 'email_gmail', icon: 'mdi-google', color: 'error' },
  { title: 'Web Widget', value: 'web_widget', icon: 'mdi-dock-window', color: 'primary' },
]

const form = ref({
  name: '',
  type: 'whatsapp' as ChannelType,
  provider_id: '',
  credentials: {
    access_token: '',
    page_access_token: '',
    bot_token: '',
    verify_token: Math.random().toString(36).substring(2, 15),
    widget_token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),
    smtp_host: '',
    smtp_port: 587,
    smtp_user: '',
    smtp_pass: '',
  },
  settings: {
    primary_color: '#3f51b5',
    welcome_message: '¿En qué podemos ayudarte?',
    logo_url: '',
    allowed_domains: '',
  },
  is_active: true,
})

async function loadChannels() {
  await store.fetchChannels()
}

function openCreate() {
  editingChannel.value = null
  form.value = {
    name: '',
    type: 'whatsapp',
    provider_id: '',
    credentials: {
      access_token: '',
      page_access_token: '',
      verify_token: Math.random().toString(36).substring(2, 15),
      widget_token: Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15),
      smtp_host: '',
      smtp_port: 587,
      smtp_user: '',
      smtp_pass: '',
    },
    settings: {
      primary_color: '#3f51b5',
      welcome_message: '¿En qué podemos ayudarte?',
      logo_url: '',
      allowed_domains: '',
    },
    is_active: true,
  }
  dialog.value = true
}

function openEdit(channel: Channel) {
  editingChannel.value = channel
  form.value = {
    name: channel.name,
    type: channel.type,
    provider_id: channel.provider_id || '',
    credentials: { ...channel.credentials },
    settings: channel.settings ? { ...channel.settings } : {
      primary_color: '#3f51b5',
      welcome_message: '¿En qué podemos ayudarte?',
      logo_url: '',
      allowed_domains: '',
    },
    is_active: channel.is_active,
  }
  dialog.value = true
}

async function saveChannel() {
  try {
    const payload = { ...form.value }
    
    // Convert allowed_domains string to array for web_widget
    if (payload.type === 'web_widget' && typeof payload.settings.allowed_domains === 'string') {
      payload.settings.allowed_domains = payload.settings.allowed_domains
        .split(',')
        .map(d => d.trim())
        .filter(d => d !== '')
    }

    if (editingChannel.value?.id) {
      await store.updateChannel(editingChannel.value.id, payload)
    } else {
      await store.createChannel(payload)
    }
    dialog.value = false
  } catch (e) {
    // Error handled by store
  }
}

function confirmDelete(channel: Channel) {
  channelToDelete.value = channel
  deleteDialog.value = true
}

async function deleteChannel() {
  if (!channelToDelete.value) return
  try {
    await store.deleteChannel(channelToDelete.value.id)
    deleteDialog.value = false
    channelToDelete.value = null
  } catch (e) {
    // Error handled by store
  }
}

async function toggleActive(channel: Channel) {
  try {
    // Actualización optimista o directa
    await store.updateChannel(channel.id, { is_active: !channel.is_active })
  } catch (e) {
    // Error handled by store
  }
}

onMounted(() => {
  loadChannels()
})

function getChannelIcon(type: string) {
  return channelTypes.find(t => t.value === type)?.icon || 'mdi-chat'
}

function getChannelColor(type: string) {
  return channelTypes.find(t => t.value === type)?.color || 'grey'
}
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4">
      <h6 class="text-h6">Canales Oficiales de Comunicación</h6>
      <VBtn color="primary" prepend-icon="mdi-plus" @click="openCreate">
        Conectar Canal
      </VBtn>
    </div>

    <VRow v-if="store.channels.length > 0">
      <VCol v-for="channel in store.channels" :key="channel.id" cols="12" md="6">
        <VCard border flat>
          <VCardItem>
            <template #prepend>
              <VAvatar :color="getChannelColor(channel.type)" variant="tonal" rounded>
                <VIcon :icon="getChannelIcon(channel.type)" />
              </VAvatar>
            </template>
            <VCardTitle>{{ channel.name }}</VCardTitle>
            <VCardSubtitle class="text-uppercase">{{ channel.type.replace('_', ' ') }}</VCardSubtitle>
            
            <template #append>
              <VSwitch
                :model-value="channel.is_active"
                color="success"
                density="compact"
                hide-details
                @change="toggleActive(channel)"
              />
            </template>
          </VCardItem>

          <VDivider />

          <VCardText class="py-2">
            <div class="text-caption text-medium-emphasis">ID Proveedor: {{ channel.provider_id || 'N/A' }}</div>
            <div class="text-caption text-medium-emphasis" v-if="channel.credentials?.verify_token">
              Verify Token: <code class="bg-grey-lighten-4 px-1 rounded">{{ channel.credentials.verify_token }}</code>
            </div>
          </VCardText>

          <VCardActions>
            <VBtn variant="text" size="small" prepend-icon="mdi-pencil" @click="openEdit(channel)">Configurar</VBtn>
            <VSpacer />
            <VBtn color="error" variant="text" size="small" icon="mdi-delete" @click="confirmDelete(channel)" />
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <div v-else-if="!store.loading" class="text-center py-12 border rounded-lg dashed">
      <VIcon icon="mdi-lan-connect" size="64" color="grey-lighten-1" class="mb-4" />
      <div class="text-h6 text-medium-emphasis">No hay canales configurados</div>
      <p class="text-body-2 text-disabled mb-4">Conecta tus APIs oficiales para empezar a recibir mensajes.</p>
      <VBtn variant="outlined" color="primary" @click="openCreate">Configurar mi primer canal</VBtn>
    </div>

    <div v-if="store.loading" class="text-center py-12">
      <VProgressCircular indeterminate color="primary" />
    </div>

    <!-- Create/Edit Dialog -->
    <VDialog v-model="dialog" max-width="600">
      <VCard>
        <VCardTitle>{{ editingChannel ? 'Editar Canal' : 'Conectar Nuevo Canal' }}</VCardTitle>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <VSelect
                v-model="form.type"
                :items="channelTypes"
                label="Tipo de Canal"
                :disabled="!!editingChannel"
              />
            </VCol>
            <VCol cols="12">
              <VTextField v-model="form.name" label="Nombre del Canal (Ej: WhatsApp Oficial)" />
            </VCol>

            <!-- WhatsApp Specific -->
            <template v-if="form.type === 'whatsapp'">
              <VCol cols="12">
                <VTextField v-model="form.provider_id" label="WhatsApp Business Phone Number ID" />
              </VCol>
              <VCol cols="12">
                <VTextField v-model="form.credentials.access_token" label="Permanent Access Token" type="password" />
              </VCol>
            </template>

            <!-- Messenger Specific -->
            <template v-if="form.type === 'messenger'">
              <VCol cols="12">
                <VTextField v-model="form.provider_id" label="Facebook Page ID" />
              </VCol>
              <VCol cols="12">
                <VTextField v-model="form.credentials.page_access_token" label="Page Access Token" type="password" />
              </VCol>
            </template>

            <!-- Instagram Specific -->
            <template v-if="form.type === 'instagram'">
              <VCol cols="12">
                <VTextField v-model="form.provider_id" label="Instagram Account ID" />
              </VCol>
              <VCol cols="12">
                <VTextField v-model="form.credentials.access_token" label="Permanent Access Token" type="password" />
              </VCol>
            </template>

            <!-- Shared Meta Fields -->
            <VCol cols="12" v-if="['whatsapp', 'messenger', 'instagram'].includes(form.type)">
              <div class="bg-blue-lighten-5 pa-3 rounded text-caption">
                <strong>Webhook Setup:</strong> Usa esta URL y Token en tu Meta App Dashboard:<br>
                URL: <code>https://tu-dominio.com/api/v1/webhooks/meta</code><br>
                Token: <code>{{ form.credentials.verify_token }}</code>
              </div>
            </VCol>

            <!-- Telegram Specific -->
            <template v-if="form.type === 'telegram'">
              <VCol cols="12">
                <VTextField v-model="form.credentials.bot_token" label="Telegram Bot Token (BotFather)" type="password" />
              </VCol>
              <VCol cols="12">
                <div class="bg-blue-lighten-5 pa-3 rounded text-caption">
                  <strong>Webhook Setup:</strong> El webhook se configurará automáticamente al guardar. URL Interna:<br>
                  <code>https://tu-dominio.com/api/v1/webhooks/telegram/{{ form.credentials.verify_token }}</code>
                </div>
              </VCol>
            </template>

            <!-- SMTP Specific -->
            <template v-if="form.type === 'email_smtp'">
              <VCol cols="12" md="8">
                <VTextField v-model="form.credentials.smtp_host" label="Host SMTP" />
              </VCol>
              <VCol cols="12" md="4">
                <VTextField v-model.number="form.credentials.smtp_port" label="Puerto" type="number" />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField v-model="form.credentials.smtp_user" label="Usuario / Email" />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField v-model="form.credentials.smtp_pass" label="Contraseña" type="password" />
              </VCol>
            </template>

            <!-- Web Widget Specific -->
            <template v-if="form.type === 'web_widget'">
              <VCol cols="12">
                <div class="text-subtitle-2 mb-2">Personalizar Widget</div>
                <VRow>
                  <VCol cols="12" md="6">
                    <VTextField v-model="form.settings.welcome_message" label="Mensaje de Bienvenida" />
                  </VCol>
                  <VCol cols="12" md="6">
                    <VTextField v-model="form.settings.logo_url" label="URL del Logo" />
                  </VCol>
                  <VCol cols="12">
                    <VTextField v-model="form.settings.allowed_domains" label="Dominios permitidos (separados por coma)" placeholder="ejemplo.com, app.ejemplo.com" />
                  </VCol>
                  <VCol cols="12" md="6">
                    <div class="text-caption mb-1">Color Primario</div>
                    <VColorPicker v-model="form.settings.primary_color" hide-inputs flat />
                  </VCol>
                </VRow>
              </VCol>

              <VCol cols="12" v-if="editingChannel">
                <div class="bg-grey-lighten-4 pa-3 rounded">
                  <div class="text-subtitle-2 mb-2">Código de Instalación</div>
                  <div class="text-caption mb-2">Copia y pega este snippet antes de cerrar la etiqueta <code>&lt;/body&gt;</code> de tu sitio web:</div>
                  <pre class="bg-shades-black text-white pa-3 rounded overflow-x-auto text-caption"><code>&lt;script 
  src="{{ window.location.origin }}/dist/widget.js?token={{ form.credentials.widget_token }}" 
  async 
  defer
&gt;&lt;/script&gt;</code></pre>
                </div>
              </VCol>
            </template>
          </VRow>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialog = false">Cancelar</VBtn>
          <VBtn color="primary" @click="saveChannel" :loading="store.loading">Guardar Conexión</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Delete Dialog -->
    <VDialog v-model="deleteDialog" max-width="400">
      <VCard>
        <VCardTitle>Confirmar eliminación</VCardTitle>
        <VCardText>¿Estás seguro de que deseas desconectar el canal "{{ channelToDelete?.name }}"? Se perderá la sincronización de mensajes.</VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="deleteDialog = false">Cancelar</VBtn>
          <VBtn color="error" @click="deleteChannel" :loading="store.loading">Desconectar</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.dashed { border-style: dashed !important; border-width: 2px !important; }
</style>
