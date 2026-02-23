<script setup lang="ts">
import api from '@/api'

const route = useRoute()
const router = useRouter()
const projectId = computed(() => route.params.id)

const project = ref<any>(null)
const tasks = ref<any[]>([])
const loading = ref(true)

const fetchProject = async () => {
  loading.value = true
  try {
    const [projectRes, tasksRes] = await Promise.all([
      api.get(`/operations/projects/${projectId.value}`),
      api.get('/operations/tasks', { params: { project_id: projectId.value } }),
    ])
    project.value = projectRes.data.data
    tasks.value = tasksRes.data.data || []
  } catch (e) {
    console.error('Error fetching project:', e)
    router.push({ name: 'projects' })
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

onMounted(fetchProject)
</script>

<template>
  <div v-if="loading" class="text-center py-12">
    <VProgressCircular indeterminate color="primary" size="48" />
  </div>

  <div v-else-if="project">
    <div class="d-flex align-center mb-6">
      <VBtn icon variant="text" class="me-4" @click="router.back()">
        <VIcon icon="mdi-arrow-left" />
      </VBtn>
      <div class="flex-grow-1">
        <h4 class="text-h4 font-weight-bold">
          {{ project.name }}
        </h4>
        <div class="d-flex align-center gap-2 mt-1">
          <VChip
            :color="project.status === 'active' ? 'success' : 'grey'"
            size="small"
            variant="tonal"
          >
            {{ project.status_label }}
          </VChip>
          <span v-if="project.client" class="text-body-2 text-medium-emphasis">
            {{ project.client.name }}
          </span>
        </div>
      </div>
      <VBtn variant="outlined" class="me-2">
        <VIcon icon="mdi-pencil" class="me-1" />
        Editar
      </VBtn>
    </div>

    <VRow>
      <!-- Project Info -->
      <VCol cols="12" md="4">
        <VCard class="mb-4">
          <VCardTitle>Progreso</VCardTitle>
          <VCardText class="text-center">
            <VProgressCircular
              :model-value="project.progress"
              :size="120"
              :width="12"
              color="primary"
            >
              <span class="text-h4 font-weight-bold">{{ project.progress }}%</span>
            </VProgressCircular>
          </VCardText>
        </VCard>

        <VCard>
          <VCardTitle>Detalles</VCardTitle>
          <VCardText>
            <VList density="compact">
              <VListItem v-if="project.manager">
                <template #prepend>
                  <VIcon icon="mdi-account" size="small" class="me-2" />
                </template>
                <VListItemTitle>Manager</VListItemTitle>
                <VListItemSubtitle>{{ project.manager.name }}</VListItemSubtitle>
              </VListItem>
              <VListItem v-if="project.start_date">
                <template #prepend>
                  <VIcon icon="mdi-calendar-start" size="small" class="me-2" />
                </template>
                <VListItemTitle>Inicio</VListItemTitle>
                <VListItemSubtitle>{{ project.start_date }}</VListItemSubtitle>
              </VListItem>
              <VListItem v-if="project.due_date">
                <template #prepend>
                  <VIcon icon="mdi-calendar-end" size="small" class="me-2" />
                </template>
                <VListItemTitle>Fecha límite</VListItemTitle>
                <VListItemSubtitle>{{ project.due_date }}</VListItemSubtitle>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-checkbox-multiple-marked" size="small" class="me-2" />
                </template>
                <VListItemTitle>Tareas</VListItemTitle>
                <VListItemSubtitle>{{ project.tasks_count || 0 }}</VListItemSubtitle>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Tasks -->
      <VCol cols="12" md="8">
        <VCard>
          <VCardTitle class="d-flex align-center justify-space-between">
            <span>Tareas</span>
            <VBtn size="small" prepend-icon="mdi-plus">
              Nueva Tarea
            </VBtn>
          </VCardTitle>
          <VCardText>
            <VList v-if="tasks.length">
              <VListItem
                v-for="task in tasks"
                :key="task.id"
              >
                <template #prepend>
                  <VCheckbox
                    :model-value="task.status === 'completed'"
                    hide-details
                    density="compact"
                  />
                </template>
                <VListItemTitle :class="{ 'text-decoration-line-through': task.status === 'completed' }">
                  {{ task.title }}
                </VListItemTitle>
                <VListItemSubtitle v-if="task.assignee">
                  {{ task.assignee.name }}
                </VListItemSubtitle>
                <template #append>
                  <VChip :color="getStatusColor(task.status)" size="x-small" variant="tonal">
                    {{ task.status_label }}
                  </VChip>
                </template>
              </VListItem>
            </VList>
            <div v-else class="text-center text-medium-emphasis py-8">
              No hay tareas en este proyecto
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
