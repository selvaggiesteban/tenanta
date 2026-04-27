<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const loading = ref(true)
const contact = ref({
  id: route.params.id || 'N/A',
  name: 'Juan Pérez',
  email: 'juan.perez@example.com',
  phone: '+54 9 11 1234 5678',
  source: 'Facebook Ads',
  custom_fields: {
    last_login: '2023-10-25 14:30:00',
    segment: 'Retail',
    lead_score: 85
  }
})

onMounted(() => {
  // Simular carga de datos
  setTimeout(() => {
    loading.value = false
  }, 500)
})
</script>

<template>
  <VContainer fluid>
    <div v-if="loading" class="text-center pa-12">
      <VProgressCircular indeterminate color="primary" size="64" />
    </div>
    <div v-else>
      <div class="d-flex align-center mb-6">
        <VBtn icon="mdi-arrow-left" variant="text" to="/crm/leads" class="mr-4" />
        <h1 class="text-h4 font-weight-bold">Detalle del Contacto (Extendido)</h1>
      </div>

      <VRow>
        <VCol cols="12" md="4">
          <VCard elevation="1" class="mb-4">
            <VCardText class="text-center">
              <VAvatar color="primary" size="80" class="mb-4">
                {{ contact.name.charAt(0) }}
              </VAvatar>
              <h2 class="text-h6">{{ contact.name }}</h2>
              <p class="text-body-2 text-medium-emphasis mb-4">{{ contact.email }}</p>
              <VBtn block color="primary" variant="flat">Editar Perfil</VBtn>
            </VCardText>
          </VCard>

          <VCard elevation="1">
            <VList>
              <VListSubheader>Información Básica</VListSubheader>
              <VListItem prepend-icon="mdi-phone" :title="contact.phone" subtitle="Teléfono" />
              <VListItem prepend-icon="mdi-bullseye-arrow" :title="contact.source" subtitle="Fuente" />
            </VList>
          </VCard>
        </VCol>

        <VCol cols="12" md="8">
          <VCard elevation="1">
            <VTabs color="primary">
              <VTab value="extended">Campos Extendidos</VTab>
              <VTab value="history">Historial</VTab>
            </VTabs>
            <VCardText>
              <VList>
                <VListItem v-for="(val, key) in contact.custom_fields" :key="key">
                  <VListItemTitle class="text-capitalize">{{ key.replace('_', ' ') }}</VListItemTitle>
                  <VListItemSubtitle>{{ val }}</VListItemSubtitle>
                </VListItem>
              </VList>
              
              <div class="mt-4 border-dashed pa-4 text-center">
                <p class="text-caption text-medium-emphasis">
                  Nuevos campos del Plan de Adaptación CRM aparecerán aquí.
                </p>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </div>
  </VContainer>
</template>

<style scoped>
.border-dashed {
  border: 1px dashed rgba(var(--v-border-color), 0.5);
}
</style>
