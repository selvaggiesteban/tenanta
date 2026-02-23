<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'
import type { EmailTemplateListItem } from '@/types/marketing'

const { t } = useI18n()
const router = useRouter()
const store = useMarketingStore()

const search = ref('')
const typeFilter = ref('')
const categoryFilter = ref('')
const deleteDialog = ref(false)
const templateToDelete = ref<EmailTemplateListItem | null>(null)

const typeOptions = [
  { title: t('marketing.all_types'), value: '' },
  { title: t('marketing.type.marketing'), value: 'marketing' },
  { title: t('marketing.type.transactional'), value: 'transactional' },
  { title: t('marketing.type.notification'), value: 'notification' },
]

const categoryOptions = computed(() => {
  const categories = [{ title: t('marketing.all_categories'), value: '' }]
  store.categories.forEach(cat => {
    categories.push({ title: cat, value: cat })
  })
  return categories
})

const headers = [
  { title: t('marketing.template_name'), key: 'name', sortable: true },
  { title: t('marketing.subject'), key: 'subject', sortable: true },
  { title: t('marketing.type'), key: 'type', sortable: true },
  { title: t('marketing.category'), key: 'category', sortable: true },
  { title: t('marketing.status'), key: 'is_active', sortable: true },
  { title: t('common.created_at'), key: 'created_at', sortable: true },
  { title: t('common.actions'), key: 'actions', sortable: false, align: 'end' },
]

const filteredTemplates = computed(() => {
  let result = store.templates
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(t =>
      t.name.toLowerCase().includes(searchLower) ||
      t.subject.toLowerCase().includes(searchLower)
    )
  }
  if (typeFilter.value) {
    result = result.filter(t => t.type === typeFilter.value)
  }
  if (categoryFilter.value) {
    result = result.filter(t => t.category === categoryFilter.value)
  }
  return result
})

function getTypeColor(type: string): string {
  const colors: Record<string, string> = {
    marketing: 'primary',
    transactional: 'info',
    notification: 'warning',
  }
  return colors[type] || 'grey'
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString()
}

function goToCreate() {
  router.push({ name: 'marketing-template-create' })
}

function goToEdit(template: EmailTemplateListItem) {
  router.push({ name: 'marketing-template-edit', params: { id: template.id } })
}

async function duplicateTemplate(template: EmailTemplateListItem) {
  try {
    const newTemplate = await store.duplicateTemplate(template.id)
    router.push({ name: 'marketing-template-edit', params: { id: newTemplate.id } })
  } catch (e) {
    // Error handled by store
  }
}

function confirmDelete(template: EmailTemplateListItem) {
  templateToDelete.value = template
  deleteDialog.value = true
}

async function deleteTemplate() {
  if (!templateToDelete.value) return
  try {
    await store.deleteTemplate(templateToDelete.value.id)
    deleteDialog.value = false
    templateToDelete.value = null
  } catch (e) {
    // Error handled by store
  }
}

onMounted(() => {
  store.fetchTemplates()
  store.fetchCategories()
})
</script>

<template>
  <VContainer fluid>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex align-center">
            <VIcon icon="mdi-file-document-outline" class="mr-2" />
            {{ t('marketing.templates') }}
            <VSpacer />
            <VBtn
              color="primary"
              prepend-icon="mdi-plus"
              @click="goToCreate"
            >
              {{ t('marketing.new_template') }}
            </VBtn>
          </VCardTitle>

          <VCardText>
            <VRow class="mb-4">
              <VCol cols="12" md="4">
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
              <VCol cols="12" md="3">
                <VSelect
                  v-model="categoryFilter"
                  :items="categoryOptions"
                  :label="t('marketing.category')"
                  density="compact"
                  hide-details
                />
              </VCol>
            </VRow>

            <VDataTable
              :headers="headers"
              :items="filteredTemplates"
              :loading="store.loading"
              :items-per-page="15"
              hover
            >
              <template #item.type="{ item }">
                <VChip
                  :color="getTypeColor(item.type)"
                  size="small"
                  label
                >
                  {{ t(`marketing.type.${item.type}`) }}
                </VChip>
              </template>

              <template #item.category="{ item }">
                {{ item.category || '-' }}
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
                  icon="mdi-pencil"
                  variant="text"
                  size="small"
                  :title="t('common.edit')"
                  @click="goToEdit(item)"
                />
                <VBtn
                  icon="mdi-content-copy"
                  variant="text"
                  size="small"
                  :title="t('common.duplicate')"
                  @click="duplicateTemplate(item)"
                />
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

    <!-- Delete Confirmation Dialog -->
    <VDialog v-model="deleteDialog" max-width="400">
      <VCard>
        <VCardTitle>{{ t('common.confirm_delete') }}</VCardTitle>
        <VCardText>
          {{ t('marketing.confirm_delete_template', { name: templateToDelete?.name }) }}
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn @click="deleteDialog = false">{{ t('common.cancel') }}</VBtn>
          <VBtn color="error" @click="deleteTemplate">{{ t('common.delete') }}</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>
