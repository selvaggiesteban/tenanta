<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useTheme } from 'vuetify'

const authStore = useAuthStore()
const theme = useTheme()

const tab = ref('profile')
const saving = ref(false)

const profileForm = ref({
  name: authStore.user?.name || '',
  email: authStore.user?.email || '',
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

watch(() => preferences.value.theme, (newTheme) => {
  theme.global.name.value = newTheme
})
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold">
        Configuración
      </h4>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Administra tu cuenta y preferencias
      </p>
    </div>

    <VCard>
      <VTabs v-model="tab">
        <VTab value="profile">
          <VIcon icon="mdi-account" class="me-2" />
          Perfil
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
                  <VBtn :loading="saving">
                    Guardar Cambios
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
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
