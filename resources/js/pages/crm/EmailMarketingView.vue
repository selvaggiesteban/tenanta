<script setup lang="ts">
import { ref } from 'vue'

const campaigns = ref([
  { id: 1, name: 'Bienvenida Octubre', status: 'Enviando', sent: 450, opened: 120, ctr: '26%' },
  { id: 2, name: 'Re-engagement Black Friday', status: 'Programado', sent: 0, opened: 0, ctr: '0%' },
  { id: 3, name: 'Newsletter Mensual - Sep', status: 'Completado', sent: 1200, opened: 850, ctr: '70%' },
])
</script>

<template>
  <VContainer fluid>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Email Marketing CRM</h1>
        <p class="text-subtitle-1 text-medium-emphasis">
          Gestión avanzada de campañas de correo electrónico.
        </p>
      </div>
      <VBtn color="primary" prepend-icon="mdi-email-plus">Crear Campaña</VBtn>
    </div>

    <VRow>
      <VCol cols="12">
        <VCard elevation="1">
          <VTable>
            <thead>
              <tr>
                <th class="text-left">Nombre de Campaña</th>
                <th class="text-left">Estado</th>
                <th class="text-left">Enviados</th>
                <th class="text-left">Aperturas</th>
                <th class="text-left">CTR</th>
                <th class="text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="campaign in campaigns" :key="campaign.id">
                <td>{{ campaign.name }}</td>
                <td>
                  <VChip
                    :color="campaign.status === 'Enviando' ? 'info' : (campaign.status === 'Programado' ? 'warning' : 'success')"
                    size="small"
                    variant="tonal"
                  >
                    {{ campaign.status }}
                  </VChip>
                </td>
                <td>{{ campaign.sent }}</td>
                <td>{{ campaign.opened }}</td>
                <td>{{ campaign.ctr }}</td>
                <td class="text-right">
                  <VBtn icon="mdi-eye-outline" variant="text" size="small" :to="`/crm/campanas/conversiones/email`"></VBtn>
                  <VBtn icon="mdi-pencil-outline" variant="text" size="small"></VBtn>
                </td>
              </tr>
            </tbody>
          </VTable>
        </VCard>
      </VCol>
    </VRow>

    <VRow class="mt-4">
      <VCol cols="12" md="6">
        <VCard elevation="1" title="Plantillas de Diseño">
          <VCardText class="d-flex align-center justify-center border-dashed my-2" height="200">
            <div class="text-center">
              <VIcon color="medium-emphasis" size="48">mdi-email-newsletter</VIcon>
              <p>Galería de Plantillas Drag & Drop</p>
              <VBtn color="secondary" size="small" class="mt-2" variant="tonal">Explorar</VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="6">
        <VCard elevation="1" title="Entregabilidad (Warm-up)">
          <VCardText class="d-flex align-center justify-center border-dashed my-2" height="200">
            <div class="text-center">
              <VIcon color="success" size="48">mdi-shield-check-outline</VIcon>
              <p>Reputación del Dominio: 98%</p>
              <VBtn color="success" size="small" class="mt-2" variant="tonal">Ver Detalles</VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>

<style scoped>
.border-dashed {
  border: 1px dashed rgba(var(--v-border-color), 0.5);
  min-height: 180px;
}
</style>
