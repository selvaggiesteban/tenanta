<script setup lang="ts">
import api from '@/api'
import TimerWidget from '@/components/TimerWidget.vue'

const loading = ref(true)
const entries = ref([])
const summary = ref<any>(null)
const dateRange = ref('this_week')

const headers = [
  { title: 'Descripción', key: 'description' },
  { title: 'Proyecto', key: 'project.name' },
  { title: 'Tarea', key: 'task.title' },
  { title: 'Inicio', key: 'started_at' },
  { title: 'Duración', key: 'duration' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'end' },
]

const dateRangeOptions = [
  { title: 'Hoy', value: 'today' },
  { title: 'Esta semana', value: 'this_week' },
  { title: 'Este mes', value: 'this_month' },
]

const fetchEntries = async () => {
  loading.value = true
  try {
    const params: any = { my_entries: true, per_page: 50 }
    if (dateRange.value === 'today') params.today = true
    if (dateRange.value === 'this_week') params.this_week = true
    if (dateRange.value === 'this_month') params.this_month = true

    const [entriesRes, summaryRes] = await Promise.all([
      api.get('/tracking/entries', { params }),
      api.get('/tracking/summary'),
    ])

    entries.value = entriesRes.data.data || []
    summary.value = summaryRes.data.data
  } catch (e) {
    console.error('Error fetching time entries:', e)
  } finally {
    loading.value = false
  }
}

const formatTime = (dateStr: string) => {
  return new Date(dateStr).toLocaleString('es-AR', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const deleteEntry = async (entry: any) => {
  if (!confirm('¿Eliminar esta entrada?')) return
  try {
    await api.delete(`/tracking/entries/${entry.id}`)
    fetchEntries()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Error')
  }
}

onMounted(fetchEntries)
watch(dateRange, fetchEntries)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">
          Time Tracking
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Registra y analiza tu tiempo de trabajo
        </p>
      </div>
    </div>

    <!-- Summary Cards -->
    <VRow v-if="summary" class="mb-6">
      <VCol cols="12" sm="6" md="3">
        <VCard class="pa-4">
          <div class="d-flex align-center">
            <VAvatar color="primary" variant="tonal" size="48" class="me-4">
              <VIcon icon="mdi-clock-outline" size="24" />
            </VAvatar>
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">
                Horas Totales
              </p>
              <h4 class="text-h4 font-weight-bold">
                {{ summary.total_hours }}h
              </h4>
            </div>
          </div>
        </VCard>
      </VCol>

      <VCol cols="12" sm="6" md="3">
        <VCard class="pa-4">
          <div class="d-flex align-center">
            <VAvatar color="success" variant="tonal" size="48" class="me-4">
              <VIcon icon="mdi-currency-usd" size="24" />
            </VAvatar>
            <div>
              <p class="text-body-2 text-medium-emphasis mb-1">
                Horas Facturables
              </p>
              <h4 class="text-h4 font-weight-bold">
                {{ summary.billable_hours }}h
              </h4>
            </div>
          </div>
        </VCard>
      </VCol>
    </VRow>

    <VCard>
      <VCardText>
        <VRow align="center">
          <VCol cols="12" md="4">
            <VSelect
              v-model="dateRange"
              :items="dateRangeOptions"
              label="Período"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="entries"
        :loading="loading"
        hover
      >
        <template #item.description="{ item }">
          {{ item.description || '(Sin descripción)' }}
        </template>

        <template #item.started_at="{ item }">
          {{ formatTime(item.started_at) }}
        </template>

        <template #item.duration="{ item }">
          <span class="font-weight-medium font-mono">{{ item.duration }}</span>
        </template>

        <template #item.actions="{ item }">
          <VBtn icon variant="text" size="small" color="error" @click="deleteEntry(item)">
            <VIcon icon="mdi-delete" />
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- Floating Timer Widget -->
    <TimerWidget />
  </div>
</template>

<style scoped>
.font-mono {
  font-family: 'Roboto Mono', monospace;
}
</style>
