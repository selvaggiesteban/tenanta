<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'
import type { CreateListData, ListType } from '@/types/marketing'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const store = useMarketingStore()

const isEditing = computed(() => !!route.params.id)
const listId = computed(() => Number(route.params.id))

const form = ref<CreateListData>({
  name: '',
  description: '',
  type: 'static',
  filters: {},
  is_active: true,
  is_default: false,
})

const typeOptions = [
  { title: t('marketing.list_type.static'), value: 'static' },
  { title: t('marketing.list_type.dynamic'), value: 'dynamic' },
]

const rules = {
  required: (v: any) => !!v || t('validation.required'),
}

// Dynamic list filters
const filterRoles = ref<string[]>([])
const filterCreatedAfter = ref('')
const filterCreatedBefore = ref('')
const filterHasSubscription = ref(false)
const filterHasEnrollment = ref(false)

async function loadList() {
  if (isEditing.value) {
    await store.fetchList(listId.value)
    if (store.currentList) {
      form.value = {
        name: store.currentList.name,
        description: store.currentList.description || '',
        type: store.currentList.type,
        filters: store.currentList.filters || {},
        is_active: store.currentList.is_active,
        is_default: store.currentList.is_default,
      }
      if (store.currentList.filters) {
        filterRoles.value = store.currentList.filters.roles || []
        filterCreatedAfter.value = store.currentList.filters.created_after || ''
        filterCreatedBefore.value = store.currentList.filters.created_before || ''
        filterHasSubscription.value = store.currentList.filters.has_subscription || false
        filterHasEnrollment.value = store.currentList.filters.has_enrollment || false
      }
    }
  }
}

async function saveList() {
  // Build filters for dynamic lists
  if (form.value.type === 'dynamic') {
    form.value.filters = {
      roles: filterRoles.value.length > 0 ? filterRoles.value : undefined,
      created_after: filterCreatedAfter.value || undefined,
      created_before: filterCreatedBefore.value || undefined,
      has_subscription: filterHasSubscription.value || undefined,
      has_enrollment: filterHasEnrollment.value || undefined,
    }
  } else {
    form.value.filters = {}
  }

  try {
    if (isEditing.value) {
      await store.updateList(listId.value, form.value)
      router.push({ name: 'marketing-list-detail', params: { id: listId.value } })
    } else {
      const list = await store.createList(form.value)
      router.push({ name: 'marketing-list-detail', params: { id: list.id } })
    }
  } catch (e) {
    // Error handled by store
  }
}

function goBack() {
  router.push({ name: 'marketing-lists' })
}

onMounted(() => {
  loadList()
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

    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle>
            {{ isEditing ? t('marketing.edit_list') : t('marketing.new_list') }}
          </VCardTitle>

          <VCardText>
            <VForm @submit.prevent="saveList">
              <VRow>
                <VCol cols="12" md="8">
                  <VTextField
                    v-model="form.name"
                    :label="t('marketing.list_name')"
                    :rules="[rules.required]"
                    required
                  />
                </VCol>
                <VCol cols="12" md="4">
                  <VSelect
                    v-model="form.type"
                    :items="typeOptions"
                    :label="t('marketing.type')"
                    :disabled="isEditing"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextarea
                    v-model="form.description"
                    :label="t('marketing.description')"
                    rows="2"
                  />
                </VCol>

                <!-- Dynamic List Filters -->
                <VCol v-if="form.type === 'dynamic'" cols="12">
                  <VCard variant="outlined" class="pa-4">
                    <VCardTitle class="text-subtitle-1 pa-0 mb-4">
                      {{ t('marketing.dynamic_filters') }}
                    </VCardTitle>

                    <VRow>
                      <VCol cols="12" md="6">
                        <VCombobox
                          v-model="filterRoles"
                          :label="t('marketing.filter_roles')"
                          multiple
                          chips
                          closable-chips
                          :hint="t('marketing.filter_roles_hint')"
                        />
                      </VCol>
                      <VCol cols="12" md="3">
                        <VTextField
                          v-model="filterCreatedAfter"
                          :label="t('marketing.created_after')"
                          type="date"
                        />
                      </VCol>
                      <VCol cols="12" md="3">
                        <VTextField
                          v-model="filterCreatedBefore"
                          :label="t('marketing.created_before')"
                          type="date"
                        />
                      </VCol>
                      <VCol cols="12" md="6">
                        <VCheckbox
                          v-model="filterHasSubscription"
                          :label="t('marketing.filter_has_subscription')"
                        />
                      </VCol>
                      <VCol cols="12" md="6">
                        <VCheckbox
                          v-model="filterHasEnrollment"
                          :label="t('marketing.filter_has_enrollment')"
                        />
                      </VCol>
                    </VRow>
                  </VCard>
                </VCol>

                <VCol cols="12" md="6">
                  <VSwitch
                    v-model="form.is_active"
                    :label="t('common.active')"
                    color="success"
                  />
                </VCol>
                <VCol cols="12" md="6">
                  <VSwitch
                    v-model="form.is_default"
                    :label="t('marketing.is_default')"
                    color="primary"
                  />
                </VCol>
              </VRow>

              <div class="d-flex gap-2 mt-4">
                <VBtn type="submit" color="primary" :loading="store.loading">
                  {{ t('common.save') }}
                </VBtn>
                <VBtn variant="outlined" @click="goBack">
                  {{ t('common.cancel') }}
                </VBtn>
              </div>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>
