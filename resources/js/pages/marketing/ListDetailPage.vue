<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'
import type { SubscriberListItem, AddSubscribersData } from '@/types/marketing'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const store = useMarketingStore()

const listId = computed(() => Number(route.params.id))
const search = ref('')
const statusFilter = ref('')
const addDialog = ref(false)
const importDialog = ref(false)
const deleteDialog = ref(false)
const subscriberToDelete = ref<SubscriberListItem | null>(null)

const manualEmails = ref('')
const importFile = ref<File | null>(null)

const statusOptions = [
  { title: t('marketing.all_statuses'), value: '' },
  { title: t('marketing.subscriber_status.subscribed'), value: 'subscribed' },
  { title: t('marketing.subscriber_status.unsubscribed'), value: 'unsubscribed' },
]

const headers = [
  { title: t('marketing.email'), key: 'email', sortable: true },
  { title: t('marketing.name'), key: 'name', sortable: true },
  { title: t('marketing.status'), key: 'status', sortable: true },
  { title: t('marketing.source'), key: 'source', sortable: true },
  { title: t('marketing.subscribed_at'), key: 'subscribed_at', sortable: true },
  { title: t('common.actions'), key: 'actions', sortable: false, align: 'end' },
]

const filteredSubscribers = computed(() => {
  let result = store.subscribers
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(s =>
      s.email.toLowerCase().includes(searchLower) ||
      (s.name && s.name.toLowerCase().includes(searchLower))
    )
  }
  if (statusFilter.value) {
    result = result.filter(s => s.status === statusFilter.value)
  }
  return result
})

function getStatusColor(status: string): string {
  const colors: Record<string, string> = {
    subscribed: 'success',
    unsubscribed: 'error',
    cleaned: 'grey',
    pending: 'warning',
  }
  return colors[status] || 'grey'
}

function formatDate(date: string | null): string {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

async function addSubscribers() {
  const emails = manualEmails.value
    .split('\n')
    .map(line => {
      const [email, name] = line.split(',').map(s => s.trim())
      return { email, name }
    })
    .filter(e => e.email)

  const data: AddSubscribersData = {
    subscribers: emails,
  }

  try {
    await store.addListSubscribers(listId.value, data)
    await store.fetchListSubscribers(listId.value)
    await store.fetchList(listId.value)
    addDialog.value = false
    manualEmails.value = ''
  } catch (e) {
    // Error handled by store
  }
}

async function importSubscribers() {
  if (!importFile.value) return

  try {
    await store.importListSubscribers(listId.value, importFile.value)
    await store.fetchListSubscribers(listId.value)
    await store.fetchList(listId.value)
    importDialog.value = false
    importFile.value = null
  } catch (e) {
    // Error handled by store
  }
}

function confirmDelete(subscriber: SubscriberListItem) {
  subscriberToDelete.value = subscriber
  deleteDialog.value = true
}

async function deleteSubscriber() {
  if (!subscriberToDelete.value) return

  try {
    await store.removeListSubscriber(listId.value, subscriberToDelete.value.id)
    await store.fetchList(listId.value)
    deleteDialog.value = false
    subscriberToDelete.value = null
  } catch (e) {
    // Error handled by store
  }
}

async function syncList() {
  try {
    await store.syncDynamicList(listId.value)
    await store.fetchListSubscribers(listId.value)
    await store.fetchList(listId.value)
  } catch (e) {
    // Error handled by store
  }
}

async function exportList() {
  try {
    const result = await store.exportList(listId.value)
    const blob = new Blob([result.content], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = result.filename
    a.click()
    window.URL.revokeObjectURL(url)
  } catch (e) {
    // Error handled by store
  }
}

function goToEdit() {
  router.push({ name: 'marketing-list-edit', params: { id: listId.value } })
}

function goBack() {
  router.push({ name: 'marketing-lists' })
}

function handleFileUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    importFile.value = target.files[0]
  }
}

onMounted(async () => {
  await store.fetchList(listId.value)
  await store.fetchListSubscribers(listId.value)
})
</script>

<template>
  <VContainer fluid>
    <VRow>
      <VCol cols="12">
        <VBtn
          variant="text"
          prepend-icon="mdi-arrow-left"
          @click="goBack"
        >
          {{ t('marketing.back_to_lists') }}
        </VBtn>
      </VCol>
    </VRow>

    <VRow v-if="store.currentList">
      <!-- List Info Card -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardTitle class="d-flex align-center">
            {{ store.currentList.name }}
            <VChip
              v-if="store.currentList.is_default"
              size="small"
              color="primary"
              class="ml-2"
            >
              {{ t('common.default') }}
            </VChip>
          </VCardTitle>
          <VCardSubtitle v-if="store.currentList.description">
            {{ store.currentList.description }}
          </VCardSubtitle>
          <VCardText>
            <VList density="compact">
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-account-group" />
                </template>
                <VListItemTitle>{{ t('marketing.total_subscribers') }}</VListItemTitle>
                <template #append>
                  <strong>{{ store.currentList.subscriber_count }}</strong>
                </template>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-account-check" color="success" />
                </template>
                <VListItemTitle>{{ t('marketing.active_subscribers') }}</VListItemTitle>
                <template #append>
                  <strong>{{ store.currentList.active_count }}</strong>
                </template>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-account-off" color="error" />
                </template>
                <VListItemTitle>{{ t('marketing.unsubscribed') }}</VListItemTitle>
                <template #append>
                  <strong>{{ store.currentList.unsubscribed_count }}</strong>
                </template>
              </VListItem>
            </VList>

            <VDivider class="my-4" />

            <div class="d-flex flex-wrap gap-2">
              <VBtn
                v-if="store.currentList.type === 'static'"
                size="small"
                prepend-icon="mdi-plus"
                @click="addDialog = true"
              >
                {{ t('marketing.add') }}
              </VBtn>
              <VBtn
                v-if="store.currentList.type === 'static'"
                size="small"
                prepend-icon="mdi-upload"
                @click="importDialog = true"
              >
                {{ t('marketing.import') }}
              </VBtn>
              <VBtn
                v-if="store.currentList.type === 'dynamic'"
                size="small"
                prepend-icon="mdi-sync"
                :loading="store.loading"
                @click="syncList"
              >
                {{ t('marketing.sync') }}
              </VBtn>
              <VBtn
                size="small"
                prepend-icon="mdi-download"
                @click="exportList"
              >
                {{ t('marketing.export') }}
              </VBtn>
              <VBtn
                size="small"
                prepend-icon="mdi-pencil"
                @click="goToEdit"
              >
                {{ t('common.edit') }}
              </VBtn>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Subscribers Table -->
      <VCol cols="12" md="8">
        <VCard>
          <VCardTitle>{{ t('marketing.subscribers') }}</VCardTitle>
          <VCardText>
            <VRow class="mb-4">
              <VCol cols="8">
                <VTextField
                  v-model="search"
                  :label="t('common.search')"
                  prepend-inner-icon="mdi-magnify"
                  clearable
                  density="compact"
                  hide-details
                />
              </VCol>
              <VCol cols="4">
                <VSelect
                  v-model="statusFilter"
                  :items="statusOptions"
                  :label="t('marketing.status')"
                  density="compact"
                  hide-details
                />
              </VCol>
            </VRow>

            <VDataTable
              :headers="headers"
              :items="filteredSubscribers"
              :loading="store.loading"
              :items-per-page="25"
              hover
            >
              <template #item.name="{ item }">
                {{ item.name || '-' }}
              </template>

              <template #item.status="{ item }">
                <VChip
                  :color="getStatusColor(item.status)"
                  size="small"
                  label
                >
                  {{ t(`marketing.subscriber_status.${item.status}`) }}
                </VChip>
              </template>

              <template #item.subscribed_at="{ item }">
                {{ formatDate(item.subscribed_at) }}
              </template>

              <template #item.actions="{ item }">
                <VBtn
                  icon="mdi-delete"
                  variant="text"
                  size="small"
                  color="error"
                  :title="t('common.delete')"
                  @click="confirmDelete(item)"
                />
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Add Subscribers Dialog -->
    <VDialog v-model="addDialog" max-width="500">
      <VCard>
        <VCardTitle>{{ t('marketing.add_subscribers') }}</VCardTitle>
        <VCardText>
          <VTextarea
            v-model="manualEmails"
            :label="t('marketing.emails_csv_format')"
            :hint="t('marketing.emails_csv_hint')"
            persistent-hint
            rows="8"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn @click="addDialog = false">{{ t('common.cancel') }}</VBtn>
          <VBtn
            color="primary"
            :disabled="!manualEmails"
            :loading="store.loading"
            @click="addSubscribers"
          >
            {{ t('marketing.add') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Import Dialog -->
    <VDialog v-model="importDialog" max-width="500">
      <VCard>
        <VCardTitle>{{ t('marketing.import_subscribers') }}</VCardTitle>
        <VCardText>
          <VFileInput
            :label="t('marketing.csv_file')"
            accept=".csv"
            prepend-icon="mdi-file-delimited"
            @change="handleFileUpload"
          />
          <p class="text-caption text-medium-emphasis mt-2">
            {{ t('marketing.csv_format_hint') }}
          </p>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn @click="importDialog = false">{{ t('common.cancel') }}</VBtn>
          <VBtn
            color="primary"
            :disabled="!importFile"
            :loading="store.loading"
            @click="importSubscribers"
          >
            {{ t('marketing.import') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Delete Confirmation Dialog -->
    <VDialog v-model="deleteDialog" max-width="400">
      <VCard>
        <VCardTitle>{{ t('common.confirm_delete') }}</VCardTitle>
        <VCardText>
          {{ t('marketing.confirm_delete_subscriber', { email: subscriberToDelete?.email }) }}
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn @click="deleteDialog = false">{{ t('common.cancel') }}</VBtn>
          <VBtn color="error" @click="deleteSubscriber">{{ t('common.delete') }}</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>
