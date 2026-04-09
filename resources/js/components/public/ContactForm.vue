<template>
  <VForm ref="form" @submit.prevent="submit">
    <VRow>
      <VCol cols="12" md="6">
        <VTextField
          v-model="formData.name"
          label="Nombre"
          :rules="[requiredRule]"
          required
        />
      </VCol>
      <VCol cols="12" md="6">
        <VTextField
          v-model="formData.email"
          label="Email"
          type="email"
          :rules="[requiredRule, emailRule]"
          required
        />
      </VCol>
      <VCol cols="12">
        <VTextField
          v-model="formData.phone"
          label="Teléfono (opcional)"
          type="tel"
        />
      </VCol>
      <VCol cols="12">
        <VTextField
          v-model="formData.subject"
          label="Asunto"
          :rules="[requiredRule]"
          required
        />
      </VCol>
      <VCol cols="12">
        <VTextarea
          v-model="formData.message"
          label="Mensaje"
          rows="4"
          :rules="[requiredRule]"
          required
        />
      </VCol>
      <VCol cols="12">
        <VAlert
          v-if="success"
          type="success"
          class="mb-4"
        >
          Mensaje enviado correctamente. Te contactaremos pronto.
        </VAlert>
        <VAlert
          v-if="error"
          type="error"
          class="mb-4"
        >
          {{ error }}
        </VAlert>
        <VBtn
          type="submit"
          color="primary"
          size="large"
          :loading="loading"
          block
        >
          Enviar mensaje
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()

const form = ref<any>(null)
const loading = ref(false)
const success = ref(false)
const error = ref('')

const formData = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

const requiredRule = (v: string) => !!v || 'Campo requerido'
const emailRule = (v: string) => /.+@.+\..+/.test(v) || 'Email inválido'

async function submit() {
  const { valid } = await form.value?.validate()
  if (!valid) return

  loading.value = true
  error.value = ''
  success.value = false

  try {
    await publicStore.sendInquiry(formData)
    success.value = true
    form.value?.reset()
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al enviar mensaje'
  } finally {
    loading.value = false
  }
}
</script>
