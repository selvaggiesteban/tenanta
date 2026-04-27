<template>
  <VContainer class="py-12">
    <VRow justify="center">
      <VCol cols="12" md="6" class="text-center">
        <VIcon icon="mdi-alert-circle" color="error" size="80" class="mb-4" />
        <h1 class="text-h3 font-weight-bold mb-4">Error en el Pago</h1>
        <p class="text-body-1 mb-6">Lo sentimos, no pudimos procesar tu pago. Por favor, intenta nuevamente con otro método.</p>
        <VBtn color="primary" to="/checkout" size="large">Reintentar Pago</VBtn>
      </VCol>
    </VRow>
  </VContainer>
</template>
