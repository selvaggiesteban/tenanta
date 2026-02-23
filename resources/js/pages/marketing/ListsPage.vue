<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'
import type { EmailListItem, ListType } from '@/types/marketing'

const { t } = useI18n()
const router = useRouter()
const store = useMarketingStore()

const search = ref('')
const typeFilter = ref<ListType | ''>('')
const deleteDialog = ref(false)
const listToDelete = ref<EmailListItem | null>(null)

const typeOptions = [
  { title: t('marketing.all_types'), value: '' },
  { title: t('marketing.list_type.static'), value: 'static' },
  { title: t('marketing.list_type.dynamic'), value: 'dynamic' },
]

const headers = [
  { title: t('marketing.list_name'), key: 'name', sortable: true },
  { title: t('marketing.type'), key: 'type', sortable: true },
  { title: t('marketing.subscribers'), key: 'subscriber_count', sortable: true },
  { title: t('marketing.active_subscribers'), key: 'active_count', sortable: true },
  { title: t('marketing.status'), key: 'is_active', sortable: true },
  { title: t('common.created_at'), key: 'created_at', sortable: true },
  { title: t('common.actions'), key: 'actions', sortable: false, align: 'end' },
]

const filteredLists = computed(() => {
  let result = store.lists
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(l =>
      l.name.toLowerCase().includes(searchLower) ||
      (l.description && l.description.toLowerCase().includes(searchLower))
    )
  }
  if (typeFilter.value) {
    result = result.filter(l => l.type === typeFilter.value)
  }
  return result
})

function getTypeIcon(type: ListType): string {
  return type === 'dynamic' ? 'mdi-sync' : 'mdi-format-list-bulleted'
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString()
}

function goToCreate() {
  router.push({ name: 'marketing-list-create' })
}

function goToDetail(list: EmailListItem) {
  router.push({ name: 'marketing-list-detail', params: { id: list.id } })
}

function goToEdit(list: EmailListItem) {
  router.push({ name: 'marketing-list-edit', params: { id: list.id } })
}

function confirmDelete(list: EmailListItem) {
  listToDelete.value = list
  deleteDialog.value = true
}

async function deleteList() {
  if (!listToDelete.value) return
  try {
    await store.deleteList(listToDelete.value.id)
    deleteDialog.value = false
    listToDelete.value = null
  } catch (e) {
    // Error handled by store
  }
}

async function syncList(list: EmailListItem) {
  if (list.type !== 'dynamic') return
  try {
    await store.syncDynamicList(list.id)
    await store.fetchLists()
  } catch (e) {
    // Error handled by store
  }
}

async function exportList(list: EmailListItem) {
  try {
    const result = await store.exportList(list.id)
    // Download CSV
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

onMounted(() => {
  store.fetchLists()
})
</script>

<template>
  <VContainer fluid>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex align-center">
            <VIcon icon="mdi-format-list-group" class="mr-2" />
            {{ t('marketing.lists') }}
            <VSpacer />
            <VBtn
              color="primary"
              prepend-icon="mdi-plus"
              @click="goToCreate"
            >
              {{ t('marketing.new_list') }}
            </VBtn>
          </VCardTitle>

          <VCardText>
            <VRow class="mb-4">
              <VCol cols="12" md="6">
                <VTextField
                  v-model="search"
                  :label="t('common.search')"
                  prepend-inner-icon="mdi-magnify"
                  clearable
                  density="compact"
                  hide-details
                />
              </VCol>
              <VCol cols="12" md="3">
                <VSelect
                  v-model="typeFilter"
                  :items="typeOptions"
                  :label="t('marketing.type')"
                  density="compact"
                  hide-details
                />
              </VCol>
            </VRow>

            <VDataTable
              :headers="headers"
              :items="filteredLists"
              :loading="store.loading"
              :items-per-page="15"
              hover
              @click:row="(_event, { item }) => goToDetail(item)"
            >
              <template #item.name="{ item }">
                <div class="d-flex align-center">
                  <VIcon
                    :icon="getTypeIcon(item.type)"
                    size="small"
                    class="mr-2"
                  />
                  <div>
                    <strong>{{ item.name }}</strong>
                    <div v-if="item.description" class="text-caption text-medium-emphasis">
                      {{ item.description }}
                    </div>
                  </div>
                  <VChip
                    v-if="item.is_default"
                    size="x-small"
                    color="primary"
                    class="ml-2"
                  >
                    {{ t('common.default') }}
                  </VChip>
                </div>
              </template>

              <template #item.type="{ item }">
                <VChip
                  :color="item.type === 'dynamic' ? 'info' : 'secondary'"
                  size="small"
                  label
                >
                  {{ t(`marketing.list_type.${item.type}`) }}
                </VChip>
              </template>

              <template #item.subscriber_count="{ item }">
                {{ item.subscriber_count.toLocaleString() }}
              </template>

              <template #item.active_count="{ item }">
                {{ item.active_count.toLocaleString() }}
              </template>

              <template #item.is_active="{ item }">
                <VChip
                  :color="item.is_active ? 'success' : 'grey'"
                  size="small"
                  label
                >
                  {{ item.is_active ? t('common.active') : t('common.inactive') }}
                </VChip>
              </template>

              <template #item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>

              <template #item.actions="{ item }">
                <VBtn
                  v-if="item.type === 'dynamic'"
                  icon="mdi-sync"
                  variant="text"
                  size="small"
                  :title="t('marketing.sync_list')"
                  @click.stop="syncList(item)"
                />
                <VBtn
                  icon="mdi-download"
                  variant="text"
                  size="small"
                  :title="t('marketing.export')"
                  @click.stop="exportList(item)"
                />
                <VBtn
                  icon="mdi-pencil"
                  variant="text"
                  size="small"
                  :title="t('common.edit')"
                  @click.stop="goToEdit(item)"
                />
                <VBtn
                  icon="mdi-delete"
                  variant="text"
                  size="small"
                  color="error"
                  :title="t('common.delete')"
                  @click.stop="confirmDelete(item)"
                />
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Delete Confirmation Dialog -->
    <VDialog v-model="deleteDialog" max-width="400">
      <VCard>
        <VCardTitle>{{ t('common.confirm_delete') }}</VCardTitle>
        <VCardText>
          {{ t('marketing.confirm_delete_list', { name: listToDelete?.name }) }}
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn @click="deleteDialog = false">{{ t('common.cancel') }}</VBtn>
          <VBtn color="error" @click="deleteList">{{ t('common.delete') }}</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>
