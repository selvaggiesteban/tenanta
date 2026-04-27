<template>
  <VContainer>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Email Marketing</h1>
        <p class="text-subtitle-1 text-medium-emphasis">
          Gestiona tus campañas de correo electrónico y analiza su rendimiento.
        </p>
      </div>
      <VBtn color="primary" prepend-icon="mdi-plus" size="large">
        Crear Nueva Campaña
      </VBtn>
    </div>

    <!-- Metrics Cards -->
    <VRow class="mb-6">
      <VCol cols="12" md="4">
        <VCard elevation="2" class="rounded-lg">
          <VCardText>
            <div class="d-flex align-center mb-2">
              <VAvatar color="primary-lighten-4" class="mr-3" rounded>
                <VIcon icon="mdi-email-send" color="primary"></VIcon>
              </VAvatar>
              <span class="text-subtitle-1 font-weight-medium">Emails Enviados</span>
            </div>
            <div class="text-h4 font-weight-bold">128,450</div>
            <div class="text-caption text-success mt-1">
              <VIcon icon="mdi-trending-up" size="small"></VIcon>
              <span>+12% vs mes anterior</span>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard elevation="2" class="rounded-lg">
          <VCardText>
            <div class="d-flex align-center mb-2">
              <VAvatar color="info-lighten-4" class="mr-3" rounded>
                <VIcon icon="mdi-email-open" color="info"></VIcon>
              </VAvatar>
              <span class="text-subtitle-1 font-weight-medium">Tasa de Apertura</span>
            </div>
            <div class="text-h4 font-weight-bold">24.8%</div>
            <div class="text-caption text-success mt-1">
              <VIcon icon="mdi-trending-up" size="small"></VIcon>
              <span>+2.4% vs mes anterior</span>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" md="4">
        <VCard elevation="2" class="rounded-lg">
          <VCardText>
            <div class="d-flex align-center mb-2">
              <VAvatar color="success-lighten-4" class="mr-3" rounded>
                <VIcon icon="mdi-cursor-default-click" color="success"></VIcon>
              </VAvatar>
              <span class="text-subtitle-1 font-weight-medium">Clics</span>
            </div>
            <div class="text-h4 font-weight-bold">12,240</div>
            <div class="text-caption text-success mt-1">
              <VIcon icon="mdi-trending-up" size="small"></VIcon>
              <span>+5.1% vs mes anterior</span>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Recent Campaigns Table -->
    <VCard elevation="2" class="rounded-lg">
      <VCardTitle class="pa-4 d-flex align-center">
        <span>Campañas Recientes</span>
        <VSpacer />
        <VTextField
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          label="Buscar campaña..."
          variant="outlined"
          density="compact"
          hide-details
          style="max-width: 300px"
        ></VTextField>
      </VCardTitle>
      <VDivider />
      <VDataTable
        :headers="headers"
        :items="campaigns"
        :search="search"
        class="elevation-0"
      >
        <template #[`item.status`]="{ item }">
          <VChip
            :color="getStatusColor(item.status)"
            size="small"
            class="text-uppercase font-weight-bold"
          >
            {{ item.status }}
          </VChip>
        </template>
        <template #[`item.actions`]="{ item }">
          <div class="d-flex">
            <VBtn icon="mdi-eye" variant="text" size="small" color="primary"></VBtn>
            <VBtn icon="mdi-pencil" variant="text" size="small" color="info"></VBtn>
            <VBtn icon="mdi-delete" variant="text" size="small" color="error"></VBtn>
          </div>
        </template>
      </VDataTable>
    </VCard>
  </VContainer>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const search = ref('')

const headers = [
  { title: 'Nombre de Campaña', key: 'name', align: 'start' as const },
  { title: 'Estado', key: 'status', align: 'center' as const },
  { title: 'Enviados', key: 'sent', align: 'end' as const },
  { title: 'Aperturas', key: 'opens', align: 'end' as const },
  { title: 'Clics', key: 'clicks', align: 'end' as const },
  { title: 'Fecha', key: 'date', align: 'end' as const },
  { title: 'Acciones', key: 'actions', align: 'end' as const, sortable: false },
]

const campaigns = ref([
  { id: 1, name: 'Newsletter Enero 2024', status: 'Enviado', sent: '45,000', opens: '22.4%', clicks: '8.2%', date: '2024-01-15' },
  { id: 2, name: 'Promoción Verano - Leads Calientes', status: 'Programado', sent: '12,500', opens: '-', clicks: '-', date: '2024-02-01' },
  { id: 3, name: 'Bienvenida Nuevos Suscriptores', status: 'Activo', sent: '8,240', opens: '45.1%', clicks: '15.4%', date: 'En curso' },
  { id: 4, name: 'Recuperación Carrito Abandonado', status: 'Activo', sent: '3,120', opens: '38.2%', clicks: '12.1%', date: 'En curso' },
  { id: 5, name: 'Encuesta Satisfacción Q4', status: 'Borrador', sent: '0', opens: '-', clicks: '-', date: '-' },
])

const getStatusColor = (status: string) => {
  switch (status.toLowerCase()) {
    case 'enviado': return 'success'
    case 'programado': return 'info'
    case 'activo': return 'primary'
    case 'borrador': return 'grey'
    default: return 'default'
  }
}
</script>

<style scoped>
.v-card {
  transition: transform 0.2s;
}
.v-card:hover {
  transform: translateY(-4px);
}
</style>
