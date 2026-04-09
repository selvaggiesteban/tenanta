<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()

const form = ref({
  tenant_name: '',
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

const handleRegister = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await authStore.register(form.value)

    if (response.success) {
      router.push({ name: 'dashboard' })
    } else {
      error.value = response.message || 'Error al crear la cuenta'
    }
  } catch (e: any) {
    if (e.response?.data?.errors) {
      const errors = e.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else {
      error.value = e.response?.data?.message || 'Error al conectar con el servidor'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard class="auth-card pa-4" max-width="450" width="100%">
      <VCardText class="pt-2">
        <h4 class="text-h4 mb-1">
          Crear Cuenta
        </h4>
        <p class="mb-0">
          Comienza tu prueba gratis de 14 días
        </p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="handleRegister">
          <VAlert
            v-if="error"
            type="error"
            variant="tonal"
            class="mb-4"
            closable
            @click:close="error = ''"
          >
            {{ error }}
          </VAlert>

          <VRow>
            <VCol cols="12">
              <VTextField
                v-model="form.tenant_name"
                label="Nombre de tu Empresa"
                placeholder="Mi Empresa S.A."
                prepend-inner-icon="mdi-domain"
                :disabled="loading"
                autofocus
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="form.name"
                label="Tu Nombre"
                placeholder="Juan Pérez"
                prepend-inner-icon="mdi-account-outline"
                :disabled="loading"
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="form.email"
                label="Email"
                type="email"
                placeholder="ejemplo@email.com"
                prepend-inner-icon="mdi-email-outline"
                :disabled="loading"
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Contraseña"
                :type="showPassword ? 'text' : 'password'"
                placeholder="············"
                prepend-inner-icon="mdi-lock-outline"
                :append-inner-icon="showPassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                :disabled="loading"
                @click:append-inner="showPassword = !showPassword"
              />
            </VCol>

            <VCol cols="12">
              <VTextField
                v-model="form.password_confirmation"
                label="Confirmar Contraseña"
                :type="showPassword ? 'text' : 'password'"
                placeholder="············"
                prepend-inner-icon="mdi-lock-outline"
                :disabled="loading"
              />
            </VCol>

            <VCol cols="12">
              <VCheckbox
                v-model="form.accepted_privacy"
                label="Acepto las políticas de privacidad y términos del servicio"
                density="comfortable"
                :disabled="loading"
              />
            </VCol>

            <VCol cols="12" class="mt-n4">
              <VCheckbox
                v-model="form.subscribed_to_newsletter"
                label="Deseo suscribirme a las novedades y actualizaciones por email"
                density="comfortable"
                :disabled="loading"
              />
            </VCol>

            <VCol cols="12">
              <VBtn
                block
                type="submit"
                :loading="loading"
                :disabled="!form.accepted_privacy"
              >
                Crear Cuenta
              </VBtn>
            </VCol>

            <VCol cols="12" class="text-center">
              <span class="text-body-2">¿Ya tienes cuenta?</span>
              <RouterLink to="/login" class="text-primary ms-1">
                Iniciar sesión
              </RouterLink>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </div>
</template>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  background: linear-gradient(135deg, #696CFF 0%, #8B5CF6 100%);
}

.auth-card {
  border-radius: 16px !important;
}
</style>
