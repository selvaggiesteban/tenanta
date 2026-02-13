<script setup lang="ts">
import { api } from '@/api'

interface Ticket {
  id: number
  number: string
  subject: string
  status: string
  priority: string
  creator?: { id: number; name: string }
  assignee?: { id: number; name: string } | null
  replies_count?: number
  is_overdue: boolean
  created_at: string
}

const tickets = ref<Ticket[]>([])
const loading = ref(false)
const dialog = ref(false)
const detailDialog = ref(false)
const selectedTicket = ref<Ticket | null>(null)
const filter = ref({
  status: '',
  priority: '',
  search: '',
})

const form = ref({
  subject: '',
  description: '',
  priority: 'medium',
  category: '',
})

const replyContent = ref('')
const replyLoading = ref(false)

const statusColors: Record<string, string> = {
  open: 'info',
  in_progress: 'warning',
  waiting: 'secondary',
  resolved: 'success',
  closed: 'default',
}

const priorityColors: Record<string, string> = {
  low: 'success',
  medium: 'info',
  high: 'warning',
  urgent: 'error',
}

onMounted(() => {
  loadTickets()
})

const loadTickets = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (filter.value.status) params.append('status', filter.value.status)
    if (filter.value.priority) params.append('priority', filter.value.priority)
    if (filter.value.search) params.append('search', filter.value.search)

    const response = await api.get(`/support/tickets?${params}`)
    tickets.value = response.data.data
  } catch (error) {
    console.error('Error loading tickets:', error)
  } finally {
    loading.value = false
  }
}

const createTicket = async () => {
  try {
    await api.post('/support/tickets', form.value)
    dialog.value = false
    form.value = { subject: '', description: '', priority: 'medium', category: '' }
    loadTickets()
  } catch (error) {
    console.error('Error creating ticket:', error)
  }
}

const viewTicket = async (ticket: Ticket) => {
  try {
    const response = await api.get(`/support/tickets/${ticket.id}`)
    selectedTicket.value = response.data.data
    detailDialog.value = true
  } catch (error) {
    console.error('Error loading ticket:', error)
  }
}

const addReply = async () => {
  if (!selectedTicket.value || !replyContent.value.trim()) return

  replyLoading.value = true
  try {
    await api.post(`/support/tickets/${selectedTicket.value.id}/reply`, {
      content: replyContent.value,
    })
    replyContent.value = ''
    const response = await api.get(`/support/tickets/${selectedTicket.value.id}`)
    selectedTicket.value = response.data.data
  } catch (error) {
    console.error('Error adding reply:', error)
  } finally {
    replyLoading.value = false
  }
}

const resolveTicket = async () => {
  if (!selectedTicket.value) return
  try {
    await api.patch(`/support/tickets/${selectedTicket.value.id}/resolve`)
    detailDialog.value = false
    loadTickets()
  } catch (error) {
    console.error('Error resolving ticket:', error)
  }
}

watch(filter, () => loadTickets(), { deep: true })
</script>

<template>
  <div>
    <div class="d-flex justify-space-between align-center mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">Tickets de Soporte</h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Gestiona las solicitudes de soporte
        </p>
      </div>
      <VBtn color="primary" prepend-icon="mdi-plus" @click="dialog = true">
        Nuevo Ticket
      </VBtn>
    </div>

    <!-- Filters -->
    <VCard class="mb-4">
      <VCardText>
        <VRow>
          <VCol cols="12" md="4">
            <VTextField
              v-model="filter.search"
              label="Buscar"
              prepend-inner-icon="mdi-magnify"
              clearable
              hide-details
            />
          </VCol>
          <VCol cols="12" md="4">
            <VSelect
              v-model="filter.status"
              label="Estado"
              :items="[
                { title: 'Todos', value: '' },
                { title: 'Abierto', value: 'open' },
                { title: 'En Progreso', value: 'in_progress' },
                { title: 'Esperando', value: 'waiting' },
                { title: 'Resuelto', value: 'resolved' },
                { title: 'Cerrado', value: 'closed' },
              ]"
              hide-details
            />
          </VCol>
          <VCol cols="12" md="4">
            <VSelect
              v-model="filter.priority"
              label="Prioridad"
              :items="[
                { title: 'Todas', value: '' },
                { title: 'Baja', value: 'low' },
                { title: 'Media', value: 'medium' },
                { title: 'Alta', value: 'high' },
                { title: 'Urgente', value: 'urgent' },
              ]"
              hide-details
            />
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- Tickets List -->
    <VCard :loading="loading">
      <VTable>
        <thead>
          <tr>
            <th>Número</th>
            <th>Asunto</th>
            <th>Estado</th>
            <th>Prioridad</th>
            <th>Creado por</th>
            <th>Fecha</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="ticket in tickets" :key="ticket.id" class="cursor-pointer" @click="viewTicket(ticket)">
            <td>
              <span class="font-weight-medium">{{ ticket.number }}</span>
              <VIcon v-if="ticket.is_overdue" icon="mdi-alert" color="error" size="small" class="ms-1" />
            </td>
            <td>{{ ticket.subject }}</td>
            <td>
              <VChip :color="statusColors[ticket.status]" size="small">
                {{ ticket.status }}
              </VChip>
            </td>
            <td>
              <VChip :color="priorityColors[ticket.priority]" size="small" variant="outlined">
                {{ ticket.priority }}
              </VChip>
            </td>
            <td>{{ ticket.creator?.name }}</td>
            <td>{{ new Date(ticket.created_at).toLocaleDateString('es-AR') }}</td>
            <td class="text-center">
              <VBtn icon variant="text" size="small">
                <VIcon icon="mdi-eye" />
              </VBtn>
            </td>
          </tr>
          <tr v-if="tickets.length === 0 && !loading">
            <td colspan="7" class="text-center py-8 text-medium-emphasis">
              No hay tickets
            </td>
          </tr>
        </tbody>
      </VTable>
    </VCard>

    <!-- Create Dialog -->
    <VDialog v-model="dialog" max-width="600">
      <VCard>
        <VCardTitle>Nuevo Ticket</VCardTitle>
        <VCardText>
          <VForm @submit.prevent="createTicket">
            <VTextField
              v-model="form.subject"
              label="Asunto"
              class="mb-4"
              :rules="[v => !!v || 'Requerido']"
            />
            <VTextarea
              v-model="form.description"
              label="Descripción"
              rows="4"
              class="mb-4"
              :rules="[v => !!v || 'Requerido']"
            />
            <VSelect
              v-model="form.priority"
              label="Prioridad"
              :items="[
                { title: 'Baja', value: 'low' },
                { title: 'Media', value: 'medium' },
                { title: 'Alta', value: 'high' },
                { title: 'Urgente', value: 'urgent' },
              ]"
              class="mb-4"
            />
            <VTextField
              v-model="form.category"
              label="Categoría (opcional)"
            />
          </VForm>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialog = false">Cancelar</VBtn>
          <VBtn color="primary" @click="createTicket">Crear</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Detail Dialog -->
    <VDialog v-model="detailDialog" max-width="800">
      <VCard v-if="selectedTicket">
        <VCardTitle class="d-flex align-center">
          <span>{{ selectedTicket.number }} - {{ selectedTicket.subject }}</span>
          <VSpacer />
          <VChip :color="statusColors[selectedTicket.status]" size="small" class="me-2">
            {{ selectedTicket.status }}
          </VChip>
          <VChip :color="priorityColors[selectedTicket.priority]" size="small" variant="outlined">
            {{ selectedTicket.priority }}
          </VChip>
        </VCardTitle>
        <VDivider />
        <VCardText>
          <div class="mb-4">
            <strong>Descripción:</strong>
            <p class="mt-2">{{ selectedTicket.description }}</p>
          </div>

          <VDivider class="my-4" />

          <h6 class="text-h6 mb-4">Respuestas</h6>

          <div v-if="selectedTicket.replies?.length" class="replies-list">
            <VCard
              v-for="reply in selectedTicket.replies"
              :key="reply.id"
              variant="outlined"
              class="mb-3"
              :class="{ 'border-warning': reply.is_internal }"
            >
              <VCardText>
                <div class="d-flex align-center mb-2">
                  <VAvatar size="32" color="primary" class="me-2">
                    <span class="text-caption">{{ reply.user.name.charAt(0) }}</span>
                  </VAvatar>
                  <div>
                    <strong>{{ reply.user.name }}</strong>
                    <span v-if="reply.is_internal" class="text-warning ms-2">(Interno)</span>
                    <br>
                    <span class="text-caption text-medium-emphasis">
                      {{ new Date(reply.created_at).toLocaleString('es-AR') }}
                    </span>
                  </div>
                </div>
                <p class="mb-0" style="white-space: pre-wrap;">{{ reply.content }}</p>
              </VCardText>
            </VCard>
          </div>
          <p v-else class="text-medium-emphasis">No hay respuestas aún</p>

          <VDivider class="my-4" />

          <VTextarea
            v-model="replyContent"
            label="Agregar respuesta"
            rows="3"
            :disabled="['resolved', 'closed'].includes(selectedTicket.status)"
          />
        </VCardText>
        <VCardActions>
          <VBtn
            v-if="!['resolved', 'closed'].includes(selectedTicket.status)"
            color="success"
            variant="outlined"
            @click="resolveTicket"
          >
            Marcar Resuelto
          </VBtn>
          <VSpacer />
          <VBtn variant="text" @click="detailDialog = false">Cerrar</VBtn>
          <VBtn
            color="primary"
            :loading="replyLoading"
            :disabled="!replyContent.trim() || ['resolved', 'closed'].includes(selectedTicket.status)"
            @click="addReply"
          >
            Responder
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
