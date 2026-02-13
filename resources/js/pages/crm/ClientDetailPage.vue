<script setup lang="ts">
import api from '@/api'

const route = useRoute()
const router = useRouter()
const clientId = computed(() => route.params.id)

const client = ref<any>(null)
const loading = ref(true)
const contactDialog = ref(false)
const editingContact = ref<any>(null)

const contactForm = ref({
  name: '',
  email: '',
  phone: '',
  position: '',
})

const fetchClient = async () => {
  loading.value = true
  try {
    const response = await api.get(`/crm/clients/${clientId.value}`)
    client.value = response.data.data
  } catch (e) {
    console.error('Error fetching client:', e)
    router.push({ name: 'clients' })
  } finally {
    loading.value = false
  }
}

const openContactDialog = (contact: any = null) => {
  editingContact.value = contact
  if (contact) {
    contactForm.value = { ...contact }
  } else {
    contactForm.value = { name: '', email: '', phone: '', position: '' }
  }
  contactDialog.value = true
}

const saveContact = async () => {
  try {
    const data = { ...contactForm.value, client_id: client.value.id }
    if (editingContact.value) {
      await api.put(`/crm/contacts/${editingContact.value.id}`, data)
    } else {
      await api.post('/crm/contacts', data)
    }
    contactDialog.value = false
    fetchClient()
  } catch (e) {
    console.error('Error saving contact:', e)
  }
}

const makePrimary = async (contact: any) => {
  try {
    await api.patch(`/crm/contacts/${contact.id}/make-primary`)
    fetchClient()
  } catch (e) {
    console.error('Error:', e)
  }
}

onMounted(fetchClient)
</script>

<template>
  <div v-if="loading" class="text-center py-12">
    <VProgressCircular indeterminate color="primary" size="48" />
  </div>

  <div v-else-if="client">
    <div class="d-flex align-center mb-6">
      <VBtn icon variant="text" class="me-4" @click="router.back()">
        <VIcon icon="mdi-arrow-left" />
      </VBtn>
      <div>
        <h4 class="text-h4 font-weight-bold">
          {{ client.name }}
        </h4>
        <p class="text-body-2 text-medium-emphasis mb-0">
          {{ client.email || 'Sin email' }}
        </p>
      </div>
    </div>

    <VRow>
      <!-- Client Info -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardTitle>Información</VCardTitle>
          <VCardText>
            <VList density="compact">
              <VListItem v-if="client.email">
                <template #prepend>
                  <VIcon icon="mdi-email" size="small" class="me-2" />
                </template>
                {{ client.email }}
              </VListItem>
              <VListItem v-if="client.phone">
                <template #prepend>
                  <VIcon icon="mdi-phone" size="small" class="me-2" />
                </template>
                {{ client.phone }}
              </VListItem>
              <VListItem v-if="client.address">
                <template #prepend>
                  <VIcon icon="mdi-map-marker" size="small" class="me-2" />
                </template>
                {{ client.address }}
              </VListItem>
              <VListItem v-if="client.city || client.country">
                <template #prepend>
                  <VIcon icon="mdi-city" size="small" class="me-2" />
                </template>
                {{ [client.city, client.country].filter(Boolean).join(', ') }}
              </VListItem>
              <VListItem v-if="client.tax_id">
                <template #prepend>
                  <VIcon icon="mdi-card-account-details" size="small" class="me-2" />
                </template>
                {{ client.tax_id }}
              </VListItem>
            </VList>

            <VDivider class="my-4" />

            <div v-if="client.notes" class="text-body-2">
              <strong>Notas:</strong>
              <p class="mt-2 mb-0">{{ client.notes }}</p>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Contacts -->
      <VCol cols="12" md="8">
        <VCard>
          <VCardTitle class="d-flex align-center justify-space-between">
            <span>Contactos</span>
            <VBtn size="small" prepend-icon="mdi-plus" @click="openContactDialog()">
              Agregar
            </VBtn>
          </VCardTitle>
          <VCardText>
            <VList v-if="client.contacts?.length">
              <VListItem
                v-for="contact in client.contacts"
                :key="contact.id"
              >
                <template #prepend>
                  <VAvatar color="secondary" variant="tonal">
                    {{ contact.name?.charAt(0).toUpperCase() }}
                  </VAvatar>
                </template>
                <VListItemTitle>
                  {{ contact.name }}
                  <VChip v-if="contact.is_primary" size="x-small" color="primary" class="ms-2">
                    Principal
                  </VChip>
                </VListItemTitle>
                <VListItemSubtitle>
                  {{ contact.position || contact.email }}
                </VListItemSubtitle>
                <template #append>
                  <VBtn
                    v-if="!contact.is_primary"
                    icon
                    variant="text"
                    size="small"
                    @click="makePrimary(contact)"
                  >
                    <VIcon icon="mdi-star-outline" />
                  </VBtn>
                  <VBtn icon variant="text" size="small" @click="openContactDialog(contact)">
                    <VIcon icon="mdi-pencil" />
                  </VBtn>
                </template>
              </VListItem>
            </VList>
            <div v-else class="text-center text-medium-emphasis py-6">
              No hay contactos registrados
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Contact Dialog -->
    <VDialog v-model="contactDialog" max-width="500">
      <VCard>
        <VCardTitle>
          {{ editingContact ? 'Editar Contacto' : 'Nuevo Contacto' }}
        </VCardTitle>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <VTextField v-model="contactForm.name" label="Nombre *" />
            </VCol>
            <VCol cols="12">
              <VTextField v-model="contactForm.email" label="Email" type="email" />
            </VCol>
            <VCol cols="12">
              <VTextField v-model="contactForm.phone" label="Teléfono" />
            </VCol>
            <VCol cols="12">
              <VTextField v-model="contactForm.position" label="Cargo" />
            </VCol>
          </VRow>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="contactDialog = false">
            Cancelar
          </VBtn>
          <VBtn color="primary" @click="saveContact">
            Guardar
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>
