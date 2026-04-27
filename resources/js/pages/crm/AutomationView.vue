<script setup lang="ts">
import { ref } from 'vue'
import SubscriptionLock from '@/components/marketing/SubscriptionLock.vue'

const title = 'Automatizaciones CRM'
const breadcrumbs = [
  { title: 'CRM', disabled: false, href: '/crm/clients' },
  { title: 'Automatizaciones', disabled: true },
]

const automationNodes = ref([
  { id: 1, name: 'Trigger: Nuevo Lead', type: 'trigger' },
  { id: 2, name: 'Acción: Enviar Email', type: 'action' },
  { id: 3, name: 'Condición: ¿Abrió email?', type: 'condition' },
])
</script>

<template>
  <VContainer fluid>
    <VBreadcrumbs :items="breadcrumbs" class="px-0" />
    
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">{{ title }}</h1>
        <p class="text-subtitle-1 text-medium-emphasis">
          Constructor visual de flujos de trabajo y automatizaciones.
        </p>
      </div>
      <VBtn color="primary" prepend-icon="mdi-plus">
        Nueva Automatización
      </VBtn>
    </div>

    <VRow>
      <VCol cols="12" md="8" class="mx-auto">
        <SubscriptionLock />
        
        <VCard elevation="1" class="mt-6 border-dashed">
          <VCardTitle class="text-body-2 text-medium-emphasis text-center py-4">
            Vista Previa del Motor de Flujos (Próximamente)
          </VCardTitle>
          <VCardText class="pb-6">
            <div class="d-flex justify-center gap-2">
              <VChip v-for="node in automationNodes" :key="node.id" :color="node.type === 'trigger' ? 'success' : (node.type === 'action' ? 'info' : 'warning')" variant="tonal">
                {{ node.name }}
              </VChip>
            </div>
          </VCardText>
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
  border: 2px dashed rgba(var(--v-border-color), 0.3) !important;
}
</style>
