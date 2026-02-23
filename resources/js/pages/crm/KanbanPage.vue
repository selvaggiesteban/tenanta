<script setup lang="ts">
import api from '@/api'

const loading = ref(true)
const pipeline = ref<any>(null)
const stages = ref<any[]>([])
const leadsByStage = ref<Record<number, any[]>>({})

const fetchPipeline = async () => {
  loading.value = true
  try {
    // Get default pipeline for leads
    const response = await api.get('/crm/pipelines', {
      params: { type: 'leads' },
    })
    const pipelines = response.data.data || []
    pipeline.value = pipelines.find((p: any) => p.is_default) || pipelines[0]

    if (pipeline.value) {
      stages.value = pipeline.value.stages || []
      await fetchLeads()
    }
  } catch (e) {
    console.error('Error fetching pipeline:', e)
  } finally {
    loading.value = false
  }
}

const fetchLeads = async () => {
  try {
    const response = await api.get('/crm/leads', {
      params: { per_page: 200 },
    })
    const leads = response.data.data || []

    // Group by stage
    const grouped: Record<number, any[]> = {}
    stages.value.forEach((stage) => {
      grouped[stage.id] = []
    })

    leads.forEach((lead: any) => {
      const stageId = lead.pipeline_stage_id
      if (stageId && grouped[stageId]) {
        grouped[stageId].push(lead)
      } else if (stages.value.length > 0) {
        // Put unassigned leads in first stage
        grouped[stages.value[0].id]?.push(lead)
      }
    })

    leadsByStage.value = grouped
  } catch (e) {
    console.error('Error fetching leads:', e)
  }
}

const moveLeadToStage = async (lead: any, stageId: number) => {
  try {
    await api.patch(`/crm/leads/${lead.id}/move-stage`, {
      pipeline_stage_id: stageId,
    })

    // Update local state
    Object.keys(leadsByStage.value).forEach((key) => {
      const index = leadsByStage.value[Number(key)].findIndex((l: any) => l.id === lead.id)
      if (index > -1) {
        leadsByStage.value[Number(key)].splice(index, 1)
      }
    })
    leadsByStage.value[stageId].push(lead)
  } catch (e: any) {
    console.error('Error moving lead:', e)
    alert(e.response?.data?.message || 'Error al mover el lead')
  }
}

const handleDragStart = (e: DragEvent, lead: any) => {
  e.dataTransfer?.setData('leadId', lead.id.toString())
  e.dataTransfer?.setData('leadData', JSON.stringify(lead))
}

const handleDrop = (e: DragEvent, stageId: number) => {
  e.preventDefault()
  const leadData = e.dataTransfer?.getData('leadData')
  if (leadData) {
    const lead = JSON.parse(leadData)
    if (lead.pipeline_stage_id !== stageId) {
      moveLeadToStage(lead, stageId)
    }
  }
}

const handleDragOver = (e: DragEvent) => {
  e.preventDefault()
}

const formatCurrency = (value: number) => {
  if (!value) return ''
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    maximumFractionDigits: 0,
  }).format(value)
}

onMounted(fetchPipeline)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">
          Pipeline
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ pipeline?.name || 'Arrastra los leads entre etapas' }}
        </p>
      </div>
      <VBtn variant="outlined" to="/crm/leads">
        <VIcon icon="mdi-table" class="me-1" />
        Vista Lista
      </VBtn>
    </div>

    <div v-if="loading" class="text-center py-12">
      <VProgressCircular indeterminate color="primary" size="48" />
    </div>

    <div v-else-if="!pipeline" class="text-center py-12">
      <VIcon icon="mdi-view-column" size="64" color="grey" />
      <p class="text-h6 mt-4">No hay pipeline configurado</p>
      <p class="text-body-2 text-medium-emphasis">
        Crea un pipeline en Configuración para usar el Kanban
      </p>
    </div>

    <div v-else class="kanban-board">
      <div
        v-for="stage in stages"
        :key="stage.id"
        class="kanban-column"
        @drop="handleDrop($event, stage.id)"
        @dragover="handleDragOver"
      >
        <div class="kanban-column-header" :style="{ borderColor: stage.color }">
          <div class="d-flex align-center">
            <div
              class="rounded-circle me-2"
              :style="{ width: '12px', height: '12px', backgroundColor: stage.color }"
            />
            <span class="text-subtitle-1 font-weight-medium">{{ stage.name }}</span>
          </div>
          <VChip size="x-small" variant="tonal">
            {{ leadsByStage[stage.id]?.length || 0 }}
          </VChip>
        </div>

        <div class="kanban-cards">
          <VCard
            v-for="lead in leadsByStage[stage.id]"
            :key="lead.id"
            class="kanban-card pa-3"
            draggable="true"
            @dragstart="handleDragStart($event, lead)"
          >
            <div class="d-flex align-center mb-2">
              <VAvatar color="primary" variant="tonal" size="32" class="me-2">
                {{ lead.contact_name?.charAt(0).toUpperCase() }}
              </VAvatar>
              <div class="text-truncate">
                <div class="text-subtitle-2 font-weight-medium text-truncate">
                  {{ lead.contact_name }}
                </div>
                <div v-if="lead.company_name" class="text-caption text-medium-emphasis text-truncate">
                  {{ lead.company_name }}
                </div>
              </div>
            </div>

            <div v-if="lead.estimated_value" class="text-body-2 font-weight-medium text-success">
              {{ formatCurrency(lead.estimated_value) }}
            </div>

            <div v-if="lead.source" class="mt-2">
              <VChip size="x-small" variant="tonal">
                {{ lead.source_label }}
              </VChip>
            </div>
          </VCard>

          <div
            v-if="!leadsByStage[stage.id]?.length"
            class="text-center text-medium-emphasis py-8"
          >
            Arrastra leads aquí
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
