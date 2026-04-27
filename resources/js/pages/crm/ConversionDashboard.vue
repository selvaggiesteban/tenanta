<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const channel = computed(() => (route.params.channel as string) || 'default')

const stats = [
  { title: 'Contactos Totales', value: '1,234', color: 'primary', icon: 'mdi-account-group' },
  { title: 'Conversiones', value: '85', color: 'success', icon: 'mdi-chart-line' },
  { title: 'CTR', value: '12.5%', color: 'info', icon: 'mdi-cursor-default-click' },
  { title: 'Bajas', value: '3', color: 'error', icon: 'mdi-account-remove' },
]

const channelTitle = computed(() => {
  switch (channel.value.toLowerCase()) {
    case 'email': return 'Email Marketing'
    case 'messenger': return 'Facebook Messenger'
    case 'whatsapp': return 'WhatsApp Business'
    default: return 'Canal No Especificado'
  }
})
</script>

<template>
  <VContainer fluid>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Dashboard de Conversiones: {{ channelTitle }}</h1>
        <p class="text-subtitle-1 text-medium-emphasis">
          Métricas clave para optimizar la conversión en el canal {{ channel }}.
        </p>
      </div>
      <div class="d-flex gap-2">
        <VBtn variant="outlined" prepend-icon="mdi-filter-variant">Filtrar</VBtn>
        <VBtn color="primary" prepend-icon="mdi-export">Exportar Reporte</VBtn>
      </div>
    </div>

    <VRow>
      <VCol v-for="stat in stats" :key="stat.title" cols="12" sm="6" md="3">
        <VCard elevation="1">
          <VCardText class="d-flex align-center">
            <VIcon :color="stat.color" size="48" class="mr-4">{{ stat.icon }}</VIcon>
            <div>
              <div class="text-h5 font-weight-bold">{{ stat.value }}</div>
              <div class="text-caption text-medium-emphasis">{{ stat.title }}</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mt-4">
      <VCol cols="12">
        <VCard min-height="300" class="d-flex align-center justify-center border-dashed">
          <div class="text-center">
            <VIcon size="48" color="medium-emphasis" class="mb-4">mdi-chart-bar</VIcon>
            <p class="text-h6">Visualización de Datos (Próximamente)</p>
            <p class="text-body-2 text-medium-emphasis">Integración con Chart.js para tendencias del canal {{ channel }}.</p>
          </div>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.gap-2 {
  gap: 8px;
}
.border-dashed {
  border: 1px dashed rgba(var(--v-border-color), 0.5);
}
</style>
