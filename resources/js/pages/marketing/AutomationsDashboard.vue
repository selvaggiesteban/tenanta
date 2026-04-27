<template>
  <VContainer>
    <div class="d-flex align-center justify-space-between mb-6">
      <h1 class="text-h4 font-weight-bold">Automatizaciones (Growth)</h1>
      <VBtn color="primary" prepend-icon="mdi-robot">Configurar Worker</VBtn>
    </div>

    <VRow>
      <VCol cols="12" md="4" v-for="module in modules" :key="module.id">
        <VCard hover class="h-100 d-flex flex-column">
          <VCardItem>
            <template #prepend>
              <VAvatar :color="module.color" variant="tonal" rounded>
                <VIcon :icon="module.icon" />
              </VAvatar>
            </template>
            <VCardTitle>{{ module.title }}</VCardTitle>
            <VCardSubtitle>{{ module.subtitle }}</VCardSubtitle>
          </VCardItem>
          <VCardText class="flex-grow-1">
            <p class="text-body-2 text-grey">{{ module.description }}</p>
            <VChip class="mt-2" size="x-small" :color="module.status === 'active' ? 'success' : 'warning'">
              {{ module.status === 'active' ? 'Operativo' : 'Próximamente' }}
            </VChip>
          </VCardText>
          <VCardActions>
            <VSpacer />
            <VBtn variant="text" color="primary" append-icon="mdi-lock" disabled>Premium</VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <div class="mt-12">
      <h2 class="text-h5 font-weight-bold mb-4">Módulos Avanzados (Beta)</h2>
      <SubscriptionLock />
    </div>
  </VContainer>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import SubscriptionLock from '@/components/marketing/SubscriptionLock.vue'

const modules = ref([
  { id: 1, title: 'Scraper GBP', subtitle: 'Google Maps Engine', description: 'Extracción automática de leads geolocalizados directo al pipeline CRM.', icon: 'mdi-map-marker-radius', color: 'error', status: 'pending' },
  { id: 2, title: 'WhatsApp Bots', subtitle: 'Funnels 5 Pasos', description: 'Motor de respuestas automatizadas y nutrición de leads.', icon: 'mdi-whatsapp', color: 'success', status: 'pending' },
  { id: 3, title: 'Landing Builder', subtitle: 'Pro Generator', description: 'Orquestador visual para crear páginas de aterrizaje optimizadas.', icon: 'mdi-web', color: 'primary', status: 'pending' },
  { id: 4, title: 'Generador RGPD', subtitle: 'Legal Services', description: 'Creación automática de textos legales parametrizados por inquilino.', icon: 'mdi-gavel', color: 'grey-darken-2', status: 'pending' },
  { id: 5, title: 'SEO Crawler', subtitle: 'Site Auditor', description: 'Rastreo y diagnóstico de salud SEO en background.', icon: 'mdi-magnify-scan', color: 'info', status: 'pending' },
  { id: 6, title: 'Form Builder', subtitle: 'Encuestas', description: 'Constructor de formularios y encuestas nativo.', icon: 'mdi-form-select', color: 'warning', status: 'pending' }
])
</script>
