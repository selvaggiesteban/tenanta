<script setup lang="ts">
import api from '@/api'

const loading = ref(true)
const leads = ref([])
const search = ref('')
const dialog = ref(false)
const editingLead = ref<any>(null)

const form = ref({
  company_name: '',
  contact_name: '',
  email: '',
  phone: '',
  position: '',
  source: '',
  estimated_value: '',
  notes: '',
})

const sources = [
  { title: 'Sitio Web', value: 'web' },
  { title: 'Referido', value: 'referral' },
  { title: 'Llamada en Frío', value: 'cold_call' },
  { title: 'Redes Sociales', value: 'social_media' },
  { title: 'Campaña Email', value: 'email_campaign' },
  { title: 'Evento', value: 'event' },
  { title: 'Otro', value: 'other' },
]

const headers = [
  { title: 'Contacto', key: 'contact_name' },
  { title: 'Empresa', key: 'company_name' },
  { title: 'Email', key: 'email' },
  { title: 'Estado', key: 'status' },
  { title: 'Valor', key: 'estimated_value', align: 'end' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'end' },
]

const fetchLeads = async () => {
  loading.value = true
  try {
    const response = await api.get('/crm/leads', {
      params: { search: search.value, per_page: 50 },
    })
    leads.value = response.data.data || []
  } catch (e) {
    console.error('Error fetching leads:', e)
  } finally {
    loading.value = false
  }
}

const openDialog = (lead: any = null) => {
  editingLead.value = lead
  if (lead) {
    form.value = { ...lead, estimated_value: lead.estimated_value || '' }
  } else {
    form.value = {
      company_name: '',
      contact_name: '',
      email: '',
      phone: '',
      position: '',
      source: '',
      estimated_value: '',
      notes: '',
    }
  }
  dialog.value = true
}

const saveLead = async () => {
  try {
    const data = {
      ...form.value,
      estimated_value: form.value.estimated_value ? parseFloat(form.value.estimated_value) : null,
    }
    if (editingLead.value) {
      await api.put(`/crm/leads/${editingLead.value.id}`, data)
    } else {
      await api.post('/crm/leads', data)
    }
    dialog.value = false
    fetchLeads()
  } catch (e) {
    console.error('Error saving lead:', e)
  }
}

const convertLead = async (lead: any) => {
  if (!confirm(`¿Convertir "${lead.contact_name}" a cliente?`)) return
  try {
    await api.post(`/crm/leads/${lead.id}/convert`)
    fetchLeads()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Error al convertir')
  }
}

const deleteLead = async (lead: any) => {
  if (!confirm(`¿Eliminar lead "${lead.contact_name}"?`)) return
  try {
    await api.delete(`/crm/leads/${lead.id}`)
    fetchLeads()
  } catch (e) {
    console.error('Error deleting lead:', e)
  }
}

const formatCurrency = (value: number) => {
  if (!value) return '-'
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
  }).format(value)
}

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    new: 'info',
    contacted: 'primary',
    qualified: 'secondary',
    proposal: 'warning',
    won: 'success',
    lost: 'error',
  }
  return colors[status] || 'grey'
}

onMounted(fetchLeads)
watch(search, fetchLeads)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">
          Leads
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Gestiona tus oportunidades de venta
        </p>
      </div>
      <div>
        <VBtn variant="outlined" class="me-2" to="/crm/kanban">
          <VIcon icon="mdi-view-column" class="me-1" />
          Vista Kanban
        </VBtn>
        <VBtn prepend-icon="mdi-plus" @click="openDialog()">
          Nuevo Lead
        </VBtn>
      </div>
    </div>

    <VCard>
      <VCardText>
        <VTextField
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          label="Buscar leads..."
          single-line
          hide-details
          class="mb-4"
        />
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="leads"
        :loading="loading"
        hover
      >
        <template #item.contact_name="{ item }">
          <div class="d-flex align-center">
            <VAvatar color="info" variant="tonal" size="36" class="me-3">
              {{ item.contact_name?.charAt(0).toUpperCase() }}
            </VAvatar>
            {{ item.contact_name }}
          </div>
        </template>

        <template #item.status="{ item }">
          <VChip :color="getStatusColor(item.status)" size="small" variant="tonal">
            {{ item.status_label }}
          </VChip>
        </template>

        <template #item.estimated_value="{ item }">
          {{ formatCurrency(item.estimated_value) }}
        </template>

        <template #item.actions="{ item }">
          <VBtn
            v-if="item.can_be_converted"
            icon
            variant="text"
            size="small"
            color="success"
            @click="convertLead(item)"
          >
            <VIcon icon="mdi-account-convert" />
            <VTooltip activator="parent">Convertir a Cliente</VTooltip>
          </VBtn>
          <VBtn icon variant="text" size="small" @click="openDialog(item)">
            <VIcon icon="mdi-pencil" />
          </VBtn>
          <VBtn icon variant="text" size="small" color="error" @click="deleteLead(item)">
            <VIcon icon="mdi-delete" />
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- Lead Dialog -->
    <VDialog v-model="dialog" max-width="600">
      <VCard>
        <VCardTitle>
          {{ editingLead ? 'Editar Lead' : 'Nuevo Lead' }}
        </VCardTitle>
        <VCardText>
          <VRow>
            <VCol cols="12" md="6">
              <VTextField v-model="form.contact_name" label="Nombre del Contacto *" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.company_name" label="Empresa" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.email" label="Email *" type="email" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.phone" label="Teléfono" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.position" label="Cargo" />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect v-model="form.source" :items="sources" label="Origen" clearable />
            </VCol>
            <VCol cols="12">
              <VTextField
                v-model="form.estimated_value"
                label="Valor Estimado"
                type="number"
                prefix="$"
              />
            </VCol>
            <VCol cols="12">
              <VTextarea v-model="form.notes" label="Notas" rows="3" />
            </VCol>
          </VRow>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialog = false">
            Cancelar
          </VBtn>
          <VBtn color="primary" @click="saveLead">
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
