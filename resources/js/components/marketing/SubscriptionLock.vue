<template>
  <VCard
    class="subscription-lock d-flex flex-column align-center justify-center text-center pa-8 rounded-lg"
    elevation="4"
    min-height="300"
  >
    <VIcon
      icon="mdi-lock-outline"
      size="64"
      color="warning"
      class="mb-4"
    ></VIcon>
    
    <h2 class="text-h5 font-weight-bold mb-2">Acceso Restringido</h2>
    <p class="text-body-1 text-medium-emphasis mb-6">
      Este módulo requiere una suscripción activa para ser utilizado.
      Potencia tu negocio con nuestras herramientas avanzadas.
    </p>

    <VBtn
      color="primary"
      size="large"
      prepend-icon="mdi-rocket-launch"
      to="/plans"
      class="px-8"
    >
      Ver Planes
    </VBtn>

    <div class="mt-4 text-caption text-grey">
      ¿Ya tienes un plan? <a href="#" class="text-primary text-decoration-none">Contactar a soporte</a>
    </div>
  </VCard>
</template>

<script setup lang="ts">
// Componente de bloqueo para módulos premium
</script>

<style scoped>
.subscription-lock {
  border: 2px dashed rgba(var(--v-theme-warning), 0.3);
  background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.9) 0%, rgba(var(--v-theme-surface-variant), 0.5) 100%);
}
</style>
