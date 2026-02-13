<script setup lang="ts">
import api from '@/api'

const loading = ref(true)
const quotes = ref([])
const search = ref('')

const headers = [
  { title: 'N°', key: 'quote_number' },
  { title: 'Título', key: 'title' },
  { title: 'Cliente', key: 'client.name' },
  { title: 'Estado', key: 'status' },
  { title: 'Total', key: 'total', align: 'end' },
  { title: 'Válido hasta', key: 'valid_until' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'end' },
]

const fetchQuotes = async () => {
  loading.value = true
  try {
    const response = await api.get('/crm/quotes', {
      params: { search: search.value, per_page: 50 },
    })
    quotes.value = response.data.data || []
  } catch (e) {
    console.error('Error fetching quotes:', e)
  } finally {
    loading.value = false
  }
}

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    draft: 'grey',
    sent: 'info',
    viewed: 'primary',
    accepted: 'success',
    rejected: 'error',
    expired: 'warning',
  }
  return colors[status] || 'grey'
}

const formatCurrency = (value: number) => {
  if (!value) return '-'
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
  }).format(value)
}

const duplicateQuote = async (quote: any) => {
  try {
    await api.post(`/crm/quotes/${quote.id}/duplicate`)
    fetchQuotes()
  } catch (e) {
    console.error('Error duplicating quote:', e)
  }
}

const deleteQuote = async (quote: any) => {
  if (!confirm(`¿Eliminar presupuesto "${quote.title}"?`)) return
  try {
    await api.delete(`/crm/quotes/${quote.id}`)
    fetchQuotes()
  } catch (e) {
    console.error('Error deleting quote:', e)
  }
}

onMounted(fetchQuotes)
watch(search, fetchQuotes)
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">
          Presupuestos
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Gestiona tus cotizaciones
        </p>
      </div>
      <VBtn prepend-icon="mdi-plus">
        Nuevo Presupuesto
      </VBtn>
    </div>

    <VCard>
      <VCardText>
        <VTextField
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          label="Buscar presupuestos..."
          single-line
          hide-details
          class="mb-4"
        />
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="quotes"
        :loading="loading"
        hover
      >
        <template #item.quote_number="{ item }">
          <span class="font-weight-medium">{{ item.quote_number }}</span>
        </template>

        <template #item.status="{ item }">
          <VChip :color="getStatusColor(item.status)" size="small" variant="tonal">
            {{ item.status_label }}
          </VChip>
        </template>

        <template #item.total="{ item }">
          <span class="font-weight-medium">{{ formatCurrency(item.total) }}</span>
        </template>

        <template #item.valid_until="{ item }">
          <span :class="{ 'text-error': item.is_expired }">
            {{ item.valid_until || '-' }}
          </span>
        </template>

        <template #item.actions="{ item }">
          <VBtn icon variant="text" size="small" @click="duplicateQuote(item)">
            <VIcon icon="mdi-content-copy" />
            <VTooltip activator="parent">Duplicar</VTooltip>
          </VBtn>
          <VBtn icon variant="text" size="small">
            <VIcon icon="mdi-pencil" />
          </VBtn>
          <VBtn icon variant="text" size="small" color="error" @click="deleteQuote(item)">
            <VIcon icon="mdi-delete" />
          </VBtn>
        </template>
      </VDataTable>
    </VCard>
  </div>
</template>
