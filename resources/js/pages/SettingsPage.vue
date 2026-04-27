<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useBrandingStore } from '@/stores/branding'
import { useTheme } from 'vuetify'
import { ref, onMounted, watch, computed } from 'vue'
import OmnichannelChannels from '@/components/marketing/OmnichannelChannels.vue'

const authStore = useAuthStore()
const brandingStore = useBrandingStore()
const theme = useTheme()

const tab = ref('profile')
const saving = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const profileForm = ref({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
})

const landingForm = ref({
  category: '',
  hero_title: '',
  hero_subtitle: '',
  hero_image: '',
  about_text: '',
  cta_text: '',
  cta_url: '',
  features: [] as any[],
  services: [] as any[],
  faqs: [] as any[],
  whatsapp_number: '',
  google_map_url: '',
  primary_color: '#1976D2',
  secondary_color: '#424242',
})

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const preferences = ref({
  theme: theme.global.name.value,
  language: 'es',
  notifications: true,
})

const themeOptions = [
  { title: 'Claro', value: 'light' },
  { title: 'Oscuro', value: 'dark' },
]

const availableIcons = [
  'mdi-school', 'mdi-clock-fast', 'mdi-certificate', 'mdi-account-group',
  'mdi-laptop', 'mdi-rocket', 'mdi-shield-check', 'mdi-lightbulb'
]

const publicUrl = computed(() => {
  if (!brandingStore.config?.tenant_slug) return '#'
  // As specified in Phase 3.3, linking to the landing page
  return `https://google.selvaggiconsultores.com?slug=${brandingStore.config.tenant_slug}`
})

watch(() => preferences.value.theme, (newTheme) => {
  theme.global.name.value = newTheme
})

onMounted(async () => {
  try {
    const config = await brandingStore.fetchConfig()
    if (config) {
      landingForm.value = {
        hero_title: config.hero_title || '',
        hero_subtitle: config.hero_subtitle || '',
        hero_image: config.hero_image || '',
        about_text: config.about_text || '',
        cta_text: config.cta_text || '',
        cta_url: config.cta_url || '',
        features: Array.isArray(config.features) ? config.features : [],
        primary_color: config.primary_color || '#1976D2',
        secondary_color: config.secondary_color || '#424242',
      }
    }
  } catch (err) {
    console.error('Error loading branding', err)
  }
})

async function saveLanding() {
  saving.value = true
  successMessage.value = ''
  errorMessage.value = ''
  try {
    await brandingStore.updateConfig(landingForm.value)
    successMessage.value = 'Configuración de landing page guardada correctamente'
  } catch (err: any) {
    errorMessage.value = err.message || 'Error al guardar'
  } finally {
    saving.value = false
  }
}

function addFeature() {
  landingForm.value.features.push({
    icon: 'mdi-star',
    iconColor: 'primary',
    title: 'Nueva Característica',
    description: 'Descripción breve'
  })
}

function removeFeature(index: number) {
  landingForm.value.features.splice(index, 1)
}
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold">
        Configuración
      </h4>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Administra tu cuenta, preferencias y landing page pública
      </p>
    </div>

    <VSnackbar v-model="successMessage" color="success" location="top">
      {{ successMessage }}
    </VSnackbar>

    <VSnackbar v-model="errorMessage" color="error" location="top">
      {{ errorMessage }}
    </VSnackbar>

    <VCard>
      <VTabs v-model="tab">
        <VTab value="profile">
          <VIcon icon="mdi-account" class="me-2" />
          Perfil
        </VTab>
        <VTab value="landing">
          <VIcon icon="mdi-web" class="me-2" />
          Landing Page
        </VTab>
        <VTab value="channels">
          <VIcon icon="mdi-lan-connect" class="me-2" />
          Canales
        </VTab>
        <VTab value="security">
          <VIcon icon="mdi-lock" class="me-2" />
          Seguridad
        </VTab>
        <VTab value="preferences">
          <VIcon icon="mdi-cog" class="me-2" />
          Preferencias
        </VTab>
      </VTabs>

      <VCardText>
        <VWindow v-model="tab">
          <!-- Profile Tab -->
          <VWindowItem value="profile">
            <VForm>
              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="profileForm.name"
                    label="Nombre"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="profileForm.email"
                    label="Email"
                    type="email"
                  />
                </VCol>
                <VCol cols="12">
                  <VBtn :loading="saving" color="primary">
                    Guardar Cambios
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VWindowItem>

          <!-- Landing Page Tab -->
          <VWindowItem value="landing">
            <VForm @submit.prevent="saveLanding">
              <!-- ... rest of landing form ... -->
            </VForm>
          </VWindowItem>

          <!-- Channels Tab -->
          <VWindowItem value="channels">
            <OmnichannelChannels />
          </VWindowItem>

          <!-- Security Tab -->
          <VWindowItem value="security">
            <VForm>
              <VRow>
                <VCol cols="12">
                  <h6 class="text-h6 mb-4">Cambiar Contraseña</h6>
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="passwordForm.current_password"
                    label="Contraseña Actual"
                    type="password"
                  />
                </VCol>
                <VCol cols="12" md="6" />
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="passwordForm.password"
                    label="Nueva Contraseña"
                    type="password"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="passwordForm.password_confirmation"
                    label="Confirmar Nueva Contraseña"
                    type="password"
                  />
                </VCol>
                <VCol cols="12">
                  <VBtn :loading="saving">
                    Actualizar Contraseña
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VWindowItem>

          <!-- Preferences Tab -->
          <VWindowItem value="preferences">
            <VRow>
              <VCol cols="12" md="6">
                <VSelect
                  v-model="preferences.theme"
                  :items="themeOptions"
                  label="Tema"
                />
              </VCol>
              <VCol cols="12">
                <VSwitch
                  v-model="preferences.notifications"
                  label="Notificaciones por email"
                />
              </VCol>
            </VRow>
          </VWindowItem>
        </VWindow>
      </VCardText>
    </VCard>

    <!-- Tenant Info -->
    <VCard class="mt-4">
      <VCardTitle>Información de la Empresa</VCardTitle>
      <VCardText>
        <VList density="compact">
          <VListItem>
            <VListItemTitle>Empresa</VListItemTitle>
            <VListItemSubtitle>{{ authStore.user?.tenant?.name }}</VListItemSubtitle>
          </VListItem>
          <VListItem>
            <VListItemTitle>ID de Tenant</VListItemTitle>
            <VListItemSubtitle>{{ authStore.tenantId }}</VListItemSubtitle>
          </VListItem>
        </VList>
      </VCardText>
    </VCard>
  </div>
</template>
