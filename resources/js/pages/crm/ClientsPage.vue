<script setup lang="ts">
import api from '@/api'

const loading = ref(true)
const clients = ref([])
const search = ref('')
const dialog = ref(false)
const editingClient = ref<any>(null)

const form = ref({
  name: '',
  email: '',
  phone: '',
  address: '',
  city: '',
  country: '',
  tax_id: '',
  notes: '',
})

const headers = [
  { title: 'Nombre', key: 'name' },
  { title: 'Email', key: 'email' },
  { title: 'Teléfono', key: 'phone' },
  { title: 'Ciudad', key: 'city' },
  { title: 'Contactos', key: 'contacts_count', align: 'center' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'end' },
]

const fetchClients = async () => {
  loading.value = true
  try {
    const response = await api.get('/crm/clients', {
      params: { search: search.value, per_page: 50 },
    })
    clients.value = response.data.data || []
  } catch (e) {
    console.error('Error fetching clients:', e)
  } finally {
    loading.value = false
  }
}

const openDialog = (client: any = null) => {
  editingClient.value = client
  if (client) {
    form.value = { ...client }
  } else {
    form.value = {
      name: '',
      email: '',
      phone: '',
      address: '',
      city: '',
      country: '',
      tax_id: '',
      notes: '',
    }
  }
  dialog.value = true
}

const saveClient = async () => {
  try {
    if (editingClient.value) {
      await api.put(`/crm/clients/${editingClient.value.id}`, form.value)
    } else {
      await api.post('/crm/clients', form.value)
    }
    dialog.value = false
    fetchClients()
  } catch (e) {
    console.error('Error saving client:', e)
  }
}

const deleteClient = async (client: any) => {
  if (!confirm(`¿Eliminar cliente "${client.name}"?`)) return
  try {
    await api.delete(`/crm/clients/${client.id}`)
    fetchClients()
  } catch (e) {
    console.error('Error deleting client:', e)
  }
}

onMounted(fetchClients)

watch(search, () => {
  fetchClients()
})
</script>

<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-6">
      <div>
        <h4 class="text-h4 font-weight-bold">
          Clientes
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          Gestiona tu cartera de clientes
        </p>
      </div>
      <VBtn prepend-icon="mdi-plus" @click="openDialog()">
        Nuevo Cliente
      </VBtn>
    </div>

    <VCard>
      <VCardText>
        <VTextField
          v-model="search"
          prepend-inner-icon="mdi-magnify"
          label="Buscar clientes..."
          single-line
          hide-details
          class="mb-4"
        />
      </VCardText>

      <VDataTable
        :headers="headers"
        :items="clients"
        :loading="loading"
        :search="search"
        hover
      >
        <template #item.name="{ item }">
          <div class="d-flex align-center">
            <VAvatar color="primary" variant="tonal" size="36" class="me-3">
              {{ item.name?.charAt(0).toUpperCase() }}
            </VAvatar>
            <RouterLink :to="`/crm/clients/${item.id}`" class="text-decoration-none">
              {{ item.name }}
            </RouterLink>
          </div>
        </template>

        <template #item.actions="{ item }">
          <VBtn icon variant="text" size="small" @click="openDialog(item)">
            <VIcon icon="mdi-pencil" />
          </VBtn>
          <VBtn icon variant="text" size="small" color="error" @click="deleteClient(item)">
            <VIcon icon="mdi-delete" />
          </VBtn>
        </template>
      </VDataTable>
    </VCard>

    <!-- Client Dialog -->
    <VDialog v-model="dialog" max-width="600">
      <VCard>
        <VCardTitle>
          {{ editingClient ? 'Editar Cliente' : 'Nuevo Cliente' }}
        </VCardTitle>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <VTextField v-model="form.name" label="Nombre *" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.email" label="Email" type="email" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.phone" label="Teléfono" />
            </VCol>
            <VCol cols="12">
              <VTextField v-model="form.address" label="Dirección" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.city" label="Ciudad" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.country" label="País" />
            </VCol>
            <VCol cols="12" md="6">
              <VTextField v-model="form.tax_id" label="CUIT/RUC/RFC" />
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
          <VBtn color="primary" @click="saveClient">
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
