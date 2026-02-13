<script setup lang="ts">
import api from '@/api'

const timer = ref<any>(null)
const loading = ref(false)
const expanded = ref(false)
const elapsed = ref(0)
let intervalId: number | null = null

const form = ref({
  project_id: '',
  task_id: '',
  description: '',
})

const projects = ref([])
const tasks = ref([])

const fetchTimer = async () => {
  try {
    const response = await api.get('/tracking/timer')
    timer.value = response.data.data

    if (timer.value?.is_running) {
      startElapsedCounter()
    }
  } catch (e) {
    console.error('Error fetching timer:', e)
  }
}

const fetchProjects = async () => {
  try {
    const response = await api.get('/operations/projects', {
      params: { status: 'active', per_page: 100 },
    })
    projects.value = response.data.data || []
  } catch (e) {
    console.error('Error fetching projects:', e)
  }
}

const fetchTasks = async () => {
  if (!form.value.project_id) {
    tasks.value = []
    return
  }
  try {
    const response = await api.get('/operations/tasks', {
      params: { project_id: form.value.project_id, per_page: 100 },
    })
    tasks.value = response.data.data || []
  } catch (e) {
    console.error('Error fetching tasks:', e)
  }
}

const startTimer = async () => {
  loading.value = true
  try {
    const response = await api.post('/tracking/timer/start', {
      project_id: form.value.project_id || null,
      task_id: form.value.task_id || null,
      description: form.value.description || null,
    })
    timer.value = response.data.data
    startElapsedCounter()
    expanded.value = false
  } catch (e: any) {
    console.error('Error starting timer:', e)
    alert(e.response?.data?.message || 'Error al iniciar el timer')
  } finally {
    loading.value = false
  }
}

const stopTimer = async () => {
  loading.value = true
  try {
    await api.post('/tracking/timer/stop', {
      description: timer.value?.description,
    })
    timer.value = null
    elapsed.value = 0
    stopElapsedCounter()
    form.value = { project_id: '', task_id: '', description: '' }
  } catch (e) {
    console.error('Error stopping timer:', e)
  } finally {
    loading.value = false
  }
}

const cancelTimer = async () => {
  if (!confirm('¿Cancelar el timer actual?')) return
  loading.value = true
  try {
    await api.post('/tracking/timer/cancel')
    timer.value = null
    elapsed.value = 0
    stopElapsedCounter()
  } catch (e) {
    console.error('Error canceling timer:', e)
  } finally {
    loading.value = false
  }
}

const startElapsedCounter = () => {
  if (intervalId) return
  if (timer.value?.started_at) {
    const startTime = new Date(timer.value.started_at).getTime()
    elapsed.value = Math.floor((Date.now() - startTime) / 1000)
  }
  intervalId = window.setInterval(() => {
    elapsed.value++
  }, 1000)
}

const stopElapsedCounter = () => {
  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }
}

const formatTime = (seconds: number) => {
  const hrs = Math.floor(seconds / 3600)
  const mins = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

watch(() => form.value.project_id, fetchTasks)

onMounted(() => {
  fetchTimer()
  fetchProjects()
})

onUnmounted(() => {
  stopElapsedCounter()
})
</script>

<template>
  <div class="timer-widget" :class="{ running: timer?.is_running }">
    <!-- Running Timer Display -->
    <VCard v-if="timer?.is_running" elevation="8" class="pa-3" min-width="280">
      <div class="d-flex align-center justify-space-between mb-2">
        <div class="d-flex align-center">
          <VIcon icon="mdi-timer" color="primary" class="me-2" />
          <span class="text-h5 font-weight-bold font-mono">
            {{ formatTime(elapsed) }}
          </span>
        </div>
        <div>
          <VBtn icon size="small" variant="text" color="error" @click="cancelTimer">
            <VIcon icon="mdi-close" />
          </VBtn>
        </div>
      </div>

      <div v-if="timer.project?.name" class="text-body-2 text-medium-emphasis mb-1">
        <VIcon icon="mdi-folder" size="small" class="me-1" />
        {{ timer.project.name }}
      </div>
      <div v-if="timer.task?.title" class="text-body-2 text-medium-emphasis mb-1">
        <VIcon icon="mdi-checkbox-marked-outline" size="small" class="me-1" />
        {{ timer.task.title }}
      </div>
      <div v-if="timer.description" class="text-body-2 mb-2">
        {{ timer.description }}
      </div>

      <VBtn
        block
        color="error"
        :loading="loading"
        prepend-icon="mdi-stop"
        @click="stopTimer"
      >
        Detener
      </VBtn>
    </VCard>

    <!-- Start Timer Card -->
    <VCard v-else elevation="8" class="pa-3" :min-width="expanded ? 320 : 'auto'">
      <template v-if="expanded">
        <div class="d-flex align-center justify-space-between mb-3">
          <span class="text-subtitle-1 font-weight-medium">Iniciar Timer</span>
          <VBtn icon size="small" variant="text" @click="expanded = false">
            <VIcon icon="mdi-close" />
          </VBtn>
        </div>

        <VSelect
          v-model="form.project_id"
          :items="projects"
          item-title="name"
          item-value="id"
          label="Proyecto (opcional)"
          clearable
          class="mb-3"
        />

        <VSelect
          v-if="form.project_id"
          v-model="form.task_id"
          :items="tasks"
          item-title="title"
          item-value="id"
          label="Tarea (opcional)"
          clearable
          class="mb-3"
        />

        <VTextField
          v-model="form.description"
          label="¿En qué estás trabajando?"
          class="mb-3"
        />

        <VBtn
          block
          color="primary"
          :loading="loading"
          prepend-icon="mdi-play"
          @click="startTimer"
        >
          Iniciar
        </VBtn>
      </template>

      <template v-else>
        <VBtn
          color="primary"
          size="large"
          icon
          @click="expanded = true"
        >
          <VIcon icon="mdi-play" size="32" />
        </VBtn>
      </template>
    </VCard>
  </div>
</template>

<style scoped>
.font-mono {
  font-family: 'Roboto Mono', monospace;
}
</style>
