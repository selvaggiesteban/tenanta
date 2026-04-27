<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import api from '@/api'

const authStore = useAuthStore()

const stats = ref({
  clients: 0,
  leads: 0,
  projects: 0,
  quotes: 0,
  courses: 0,
  enrollments: 0,
})

const recentClients = ref([])
const recentLeads = ref([])
const loading = ref(true)

onMounted(async () => {
  try {
    const [clientsRes, leadsRes, projectsRes, quotesRes, coursesRes, enrollmentsRes] = await Promise.all([
      api.get('/crm/clients', { params: { per_page: 5 } }),
      api.get('/crm/leads', { params: { per_page: 5 } }),
      api.get('/operations/projects', { params: { per_page: 5 } }),
      api.get('/crm/quotes', { params: { per_page: 5 } }),
      api.get('/courses'),
      api.get('/enrollments'),
    ])

    stats.value = {
      clients: clientsRes.data.meta?.total || 0,
      leads: leadsRes.data.meta?.total || 0,
      projects: projectsRes.data.meta?.total || 0,
      quotes: quotesRes.data.meta?.total || 0,
      courses: coursesRes.data.meta?.total || coursesRes.data.length || 0,
      enrollments: enrollmentsRes.data.meta?.total || enrollmentsRes.data.length || 0,
    }

    recentClients.value = clientsRes.data.data || []
    recentLeads.value = leadsRes.data.data || []
  } catch (e) {
    console.error('Error loading dashboard:', e)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">
          Hola, {{ authStore.userName }}
        </h4>
        <p class="text-body-1 text-medium-emphasis mb-0">
          Bienvenido a tu panel de control
        </p>
      </div>
    </div>

    <VRow v-if="loading">
      <VCol cols="12" class="text-center py-12">
        <VProgressCircular indeterminate color="primary" size="48" />
      </VCol>
    </VRow>

    <template v-else>
      <!-- Stats Cards -->
      <VRow>
        <VCol cols="12" sm="6" lg="3">
          <VCard class="pa-4">
            <div class="d-flex align-center">
              <VAvatar color="primary" variant="tonal" size="48" class="me-4">
                <VIcon icon="mdi-account-group" size="24" />
              </VAvatar>
              <div>
                <p class="text-body-2 text-medium-emphasis mb-1">
                  Clientes
                </p>
                <h4 class="text-h4 font-weight-bold">
                  {{ stats.clients }}
                </h4>
              </div>
            </div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" lg="3">
          <VCard class="pa-4">
            <div class="d-flex align-center">
              <VAvatar color="info" variant="tonal" size="48" class="me-4">
                <VIcon icon="mdi-account-search" size="24" />
              </VAvatar>
              <div>
                <p class="text-body-2 text-medium-emphasis mb-1">
                  Leads
                </p>
                <h4 class="text-h4 font-weight-bold">
                  {{ stats.leads }}
                </h4>
              </div>
            </div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" lg="3">
          <VCard class="pa-4">
            <div class="d-flex align-center">
              <VAvatar color="success" variant="tonal" size="48" class="me-4">
                <VIcon icon="mdi-folder-multiple" size="24" />
              </VAvatar>
              <div>
                <p class="text-body-2 text-medium-emphasis mb-1">
                  Proyectos
                </p>
                <h4 class="text-h4 font-weight-bold">
                  {{ stats.projects }}
                </h4>
              </div>
            </div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" lg="3">
          <VCard class="pa-4">
            <div class="d-flex align-center">
              <VAvatar color="warning" variant="tonal" size="48" class="me-4">
                <VIcon icon="mdi-file-document-outline" size="24" />
              </VAvatar>
              <div>
                <p class="text-body-2 text-medium-emphasis mb-1">
                  Presupuestos
                </p>
                <h4 class="text-h4 font-weight-bold">
                  {{ stats.quotes }}
                </h4>
              </div>
            </div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" lg="3">
          <VCard class="pa-4">
            <div class="d-flex align-center">
              <VAvatar color="secondary" variant="tonal" size="48" class="me-4">
                <VIcon icon="mdi-book-open-variant" size="24" />
              </VAvatar>
              <div>
                <p class="text-body-2 text-medium-emphasis mb-1">
                  Cursos Activos
                </p>
                <h4 class="text-h4 font-weight-bold">
                  {{ stats.courses }}
                </h4>
              </div>
            </div>
          </VCard>
        </VCol>

        <VCol cols="12" sm="6" lg="3">
          <VCard class="pa-4">
            <div class="d-flex align-center">
              <VAvatar color="error" variant="tonal" size="48" class="me-4">
                <VIcon icon="mdi-school" size="24" />
              </VAvatar>
              <div>
                <p class="text-body-2 text-medium-emphasis mb-1">
                  Alumnos
                </p>
                <h4 class="text-h4 font-weight-bold">
                  {{ stats.enrollments }}
                </h4>
              </div>
            </div>
          </VCard>
        </VCol>
      </VRow>

      <VRow class="mt-4">
        <!-- Recent Clients -->
        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle class="d-flex align-center justify-space-between">
              <span>Clientes Recientes</span>
              <VBtn variant="text" size="small" to="/crm/clients">
                Ver todos
              </VBtn>
            </VCardTitle>
            <VList>
              <VListItem
                v-for="client in recentClients"
                :key="client.id"
                :to="`/crm/clients/${client.id}`"
              >
                <template #prepend>
                  <VAvatar color="primary" variant="tonal">
                    {{ client.name?.charAt(0).toUpperCase() }}
                  </VAvatar>
                </template>
                <VListItemTitle>{{ client.name }}</VListItemTitle>
                <VListItemSubtitle>{{ client.email }}</VListItemSubtitle>
              </VListItem>
              <VListItem v-if="!recentClients.length">
                <VListItemTitle class="text-medium-emphasis">
                  No hay clientes aún
                </VListItemTitle>
              </VListItem>
            </VList>
          </VCard>
        </VCol>

        <!-- Recent Leads -->
        <VCol cols="12" md="6">
          <VCard>
            <VCardTitle class="d-flex align-center justify-space-between">
              <span>Leads Recientes</span>
              <VBtn variant="text" size="small" to="/crm/leads">
                Ver todos
              </VBtn>
            </VCardTitle>
            <VList>
              <VListItem
                v-for="lead in recentLeads"
                :key="lead.id"
              >
                <template #prepend>
                  <VAvatar color="info" variant="tonal">
                    {{ lead.contact_name?.charAt(0).toUpperCase() }}
                  </VAvatar>
                </template>
                <VListItemTitle>{{ lead.contact_name }}</VListItemTitle>
                <VListItemSubtitle>
                  {{ lead.company_name || lead.email }}
                </VListItemSubtitle>
                <template #append>
                  <VChip
                    size="small"
                    :color="lead.status === 'won' ? 'success' : lead.status === 'lost' ? 'error' : 'info'"
                    variant="tonal"
                  >
                    {{ lead.status_label }}
                  </VChip>
                </template>
              </VListItem>
              <VListItem v-if="!recentLeads.length">
                <VListItemTitle class="text-medium-emphasis">
                  No hay leads aún
                </VListItemTitle>
              </VListItem>
            </VList>
          </VCard>
        </VCol>
      </VRow>

      <!-- Quick Actions -->
      <VRow class="mt-4">
        <VCol cols="12">
          <VCard>
            <VCardTitle>Acciones Rápidas</VCardTitle>
            <VCardText>
              <VRow>
                <VCol cols="6" sm="3">
                  <VBtn
                    block
                    variant="tonal"
                    color="primary"
                    to="/crm/clients"
                    prepend-icon="mdi-plus"
                  >
                    Nuevo Cliente
                  </VBtn>
                </VCol>
                <VCol cols="6" sm="3">
                  <VBtn
                    block
                    variant="tonal"
                    color="info"
                    to="/crm/leads"
                    prepend-icon="mdi-plus"
                  >
                    Nuevo Lead
                  </VBtn>
                </VCol>
                <VCol cols="6" sm="3">
                  <VBtn
                    block
                    variant="tonal"
                    color="success"
                    to="/projects"
                    prepend-icon="mdi-plus"
                  >
                    Nuevo Proyecto
                  </VBtn>
                </VCol>
                <VCol cols="6" sm="3">
                  <VBtn
                    block
                    variant="tonal"
                    color="warning"
                    to="/crm/quotes"
                    prepend-icon="mdi-plus"
                  >
                    Nuevo Presupuesto
                  </VBtn>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </div>
</template>
