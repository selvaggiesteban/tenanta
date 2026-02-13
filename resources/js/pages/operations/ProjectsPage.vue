<script setup lang="ts">
import api from '@/api'

const loading = ref(true)
const projects = ref([])
const search = ref('')
const statusFilter = ref('')

const headers = [
  { title: 'Proyecto', key: 'name' },
  { title: 'Cliente', key: 'client.name' },
  { title: 'Estado', key: 'status' },
  { title: 'Progreso', key: 'progress', align: 'center' },
  { title: 'Fecha límite', key: 'due_date' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'end' },
]

const statusOptions = [
  { title: 'Todos', value: '' },
  { title: 'En Planificación', value: 'planning' },
  { title: 'Activo', value: 'active' },
  { title: 'En Pausa', value: 'on_hold' },
  { title: 'Completado', value: 'completed' },
]

const fetchProjects = async () => {
  loading.value = true
  try {
    const response = await api.get('/operations/projects', {
      params: {
        search: search.value,
        status: statusFilter.value || undefined,
        per_page: 50,
      },
    })
    projects.value = response.data.data || []
  } catch (e) {
    console.error('Error fetching projects:', e)
  } finally {
    loading.value = false
  }
}

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    planning: 'info',
    active: 'success',
    on_hold: 'warning',
    completed: 'grey',
    cancelled: 'error',
  }
  return colors[status] || 'grey'
}

const getProgressColor = (progress: number) => {
  if (progress >= 80) return 'success'
  if (progress >= 50) return 'info'
  if (progress >= 25) return 'warning'
  return 'error'
}

onMounted(fetchProjects)
watch([search, statusFilter], fetchProjects)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">
          Proyectos
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Gestiona tus proyectos y tareas
        </p>
      </div>
      <VBtn prepend-icon="mdi-plus">
        Nuevo Proyecto
      </VBtn>
    </div>

    <VCard>
      <VCardText>
        <VRow>
          <VCol cols="12" md="8">
            <VTextField
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Buscar proyectos..."
              single-line
              hide-details
            />
          </VCol>
          <VCol cols="12" md="4">
            <VSelect
              v-model="statusFilter"
              :items="statusOptions"
              label="Estado"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="projects"
        :loading="loading"
        hover
      >
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <VAvatar color="success" variant="tonal" size="36" class="me-3">
              <VIcon icon="mdi-folder" />
            </VAvatar>
            <RouterLink :to="`/projects/${item.id}`" class="text-decoration-none">
              {{ item.name }}
            </RouterLink>
          </div>
        </template>

        <template #item.status="{ item }">
          <VChip :color="getStatusColor(item.status)" size="small" variant="tonal">
            {{ item.status_label }}
          </VChip>
        </template>

        <template #item.progress="{ item }">
          <div class="d-flex align-center" style="min-width: 100px">
            <VProgressLinear
              :model-value="item.progress"
              :color="getProgressColor(item.progress)"
              height="8"
              rounded
              class="me-2"
            />
            <span class="text-caption">{{ item.progress }}%</span>
          </div>
        </template>

        <template #item.due_date="{ item }">
          {{ item.due_date || '-' }}
        </template>

        <template #item.actions="{ item }">
          <VBtn icon variant="text" size="small" :to="`/projects/${item.id}`">
            <VIcon icon="mdi-eye" />
          </VBtn>
          <VBtn icon variant="text" size="small">
            <VIcon icon="mdi-pencil" />
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
