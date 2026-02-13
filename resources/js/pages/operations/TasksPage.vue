<script setup lang="ts">
import api from '@/api'

const loading = ref(true)
const tasks = ref([])
const search = ref('')
const myTasksOnly = ref(true)

const headers = [
  { title: 'Tarea', key: 'title' },
  { title: 'Proyecto', key: 'project.name' },
  { title: 'Estado', key: 'status' },
  { title: 'Prioridad', key: 'priority' },
  { title: 'Fecha límite', key: 'due_date' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'end' },
]

const fetchTasks = async () => {
  loading.value = true
  try {
    const response = await api.get('/operations/tasks', {
      params: {
        search: search.value,
        my_tasks: myTasksOnly.value,
        per_page: 50,
      },
    })
    tasks.value = response.data.data || []
  } catch (e) {
    console.error('Error fetching tasks:', e)
  } finally {
    loading.value = false
  }
}

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    pending: 'grey',
    in_progress: 'info',
    review: 'warning',
    approved: 'success',
    rejected: 'error',
    completed: 'success',
  }
  return colors[status] || 'grey'
}

const getPriorityColor = (priority: string) => {
  const colors: Record<string, string> = {
    low: 'grey',
    medium: 'info',
    high: 'warning',
    urgent: 'error',
  }
  return colors[priority] || 'grey'
}

const startTask = async (task: any) => {
  try {
    await api.patch(`/operations/tasks/${task.id}/start`)
    fetchTasks()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Error')
  }
}

const submitTask = async (task: any) => {
  try {
    await api.patch(`/operations/tasks/${task.id}/submit`)
    fetchTasks()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Error')
  }
}

onMounted(fetchTasks)
watch([search, myTasksOnly], fetchTasks)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">
          Tareas
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Gestiona tus tareas asignadas
        </p>
      </div>
    </div>

    <VCard>
      <VCardText>
        <VRow>
          <VCol cols="12" md="8">
            <VTextField
              v-model="search"
              prepend-inner-icon="mdi-magnify"
              label="Buscar tareas..."
              single-line
              hide-details
            />
          </VCol>
          <VCol cols="12" md="4">
            <VSwitch
              v-model="myTasksOnly"
              label="Solo mis tareas"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="tasks"
        :loading="loading"
        hover
      >
        <template #item.title="{ item }">
          <div>
            <div class="font-weight-medium">{{ item.title }}</div>
            <div v-if="item.description" class="text-caption text-medium-emphasis text-truncate-2">
              {{ item.description }}
            </div>
          </div>
        </template>

        <template #item.status="{ item }">
          <VChip :color="getStatusColor(item.status)" size="small" variant="tonal">
            {{ item.status_label }}
          </VChip>
        </template>

        <template #item.priority="{ item }">
          <VChip :color="getPriorityColor(item.priority)" size="x-small" variant="tonal">
            {{ item.priority_label }}
          </VChip>
        </template>

        <template #item.due_date="{ item }">
          {{ item.due_date || '-' }}
        </template>

        <template #item.actions="{ item }">
          <VBtn
            v-if="item.can_start"
            icon
            variant="text"
            size="small"
            color="primary"
            @click="startTask(item)"
          >
            <VIcon icon="mdi-play" />
            <VTooltip activator="parent">Iniciar</VTooltip>
          </VBtn>
          <VBtn
            v-if="item.can_submit"
            icon
            variant="text"
            size="small"
            color="warning"
            @click="submitTask(item)"
          >
            <VIcon icon="mdi-send" />
            <VTooltip activator="parent">Enviar a Revisión</VTooltip>
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
