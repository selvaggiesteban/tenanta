<template>
  <VContainer class="py-12">
    <VRow justify="center">
      <VCol cols="12" md="6">
        <VCard class="pa-6">
          <VCardTitle class="text-h4 font-weight-bold mb-4 text-center">Finalizar Compra</VCardTitle>
          <VCardText>
            <p class="mb-6 text-center text-body-1">Estás a un paso de acceder a todos los beneficios de tu plan.</p>
            <VBtn color="primary" size="large" block @click="processPayment" :loading="loading">
              Pagar con MercadoPago
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)

const processPayment = () => {
  loading.value = true
  // Simulación del flujo de redirección del webhook de pago
  setTimeout(() => {
    router.push('/finance/success')
  }, 1500)
}
</script>
