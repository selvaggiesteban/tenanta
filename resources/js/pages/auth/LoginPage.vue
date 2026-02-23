<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const form = ref({
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await authStore.login(form.value.email, form.value.password)

    if (response.success) {
      const redirect = route.query.redirect as string
      router.push(redirect || { name: 'dashboard' })
    } else {
      error.value = response.message || 'Error al iniciar sesión'
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al conectar con el servidor'
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
          Tenanta
        </h4>
        <p class="mb-0">
          Inicia sesión en tu cuenta
        </p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="handleLogin">
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
                v-model="form.email"
                label="Email"
                type="email"
                placeholder="ejemplo@email.com"
                prepend-inner-icon="mdi-email-outline"
                :disabled="loading"
                autofocus
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
              <VBtn
                block
                type="submit"
                :loading="loading"
              >
                Iniciar Sesión
              </VBtn>
            </VCol>

            <VCol cols="12" class="text-center">
              <span class="text-body-2">¿No tienes cuenta?</span>
              <RouterLink to="/register" class="text-primary ms-1">
                Crear cuenta
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
