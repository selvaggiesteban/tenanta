<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'
import type { EmailUnsubscribe } from '@/types/marketing'

const { t } = useI18n()
const store = useMarketingStore()

const search = ref('')
const reasonFilter = ref('')
const resubscribeDialog = ref(false)
const emailToResubscribe = ref('')

const reasonOptions = computed(() => {
  const options = [{ title: t('marketing.all_reasons'), value: '' }]
  store.unsubscribeReasons.forEach(reason => {
    options.push({ title: reason.label, value: reason.key })
  })
  return options
})

const headers = [
  { title: t('marketing.email'), key: 'email', sortable: true },
  { title: t('marketing.reason'), key: 'reason_label', sortable: true },
  { title: t('marketing.feedback'), key: 'feedback', sortable: false },
  { title: t('marketing.user'), key: 'user', sortable: false },
  { title: t('marketing.campaign'), key: 'campaign', sortable: false },
  { title: t('common.created_at'), key: 'created_at', sortable: true },
  { title: t('common.actions'), key: 'actions', sortable: false, align: 'end' },
]

const filteredUnsubscribes = computed(() => {
  let result = store.unsubscribes
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(u =>
      u.email.toLowerCase().includes(searchLower)
    )
  }
  if (reasonFilter.value) {
    result = result.filter(u => u.reason === reasonFilter.value)
  }
  return result
})

function formatDate(date: string): string {
  return new Date(date).toLocaleString()
}

function confirmResubscribe(unsubscribe: EmailUnsubscribe) {
  emailToResubscribe.value = unsubscribe.email
  resubscribeDialog.value = true
}

async function resubscribe() {
  try {
    await store.resubscribe(emailToResubscribe.value)
    resubscribeDialog.value = false
    emailToResubscribe.value = ''
  } catch (e) {
    // Error handled by store
  }
}

onMounted(() => {
  store.fetchUnsubscribes()
  store.fetchUnsubscribeReasons()
  store.fetchUnsubscribeStats()
})
</script>

<template>
  <VContainer fluid>
    <VRow>
      <!-- Stats Cards -->
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar color="error" variant="tonal" class="mr-4">
              <VIcon icon="mdi-email-off" />
            </VAvatar>
            <div>
              <div class="text-h5">{{ store.unsubscribeStats?.total || 0 }}</div>
              <div class="text-caption">{{ t('marketing.total_unsubscribes') }}</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar color="warning" variant="tonal" class="mr-4">
              <VIcon icon="mdi-calendar-clock" />
            </VAvatar>
            <div>
              <div class="text-h5">{{ store.unsubscribeStats?.last_30_days || 0 }}</div>
              <div class="text-caption">{{ t('marketing.last_30_days') }}</div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
      <VCol cols="12" md="4">
        <VCard>
          <VCardText>
            <div class="text-subtitle-2 mb-2">{{ t('marketing.by_reason') }}</div>
            <div
              v-for="(count, reason) in store.unsubscribeStats?.by_reason"
              :key="reason"
              class="d-flex justify-space-between text-body-2"
            >
              <span>{{ store.unsubscribeReasons.find(r => r.key === reason)?.label || reason }}</span>
              <span class="font-weight-medium">{{ count }}</span>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex align-center">
            <VIcon icon="mdi-email-off-outline" class="mr-2" />
            {{ t('marketing.unsubscribes') }}
          </VCardTitle>

          <VCardText>
            <VRow class="mb-4">
              <VCol cols="12" md="6">
                <VTextField
                  v-model="search"
                  :label="t('marketing.search_email')"
                  prepend-inner-icon="mdi-magnify"
                  clearable
                  density="compact"
                  hide-details
                />
              </VCol>
              <VCol cols="12" md="3">
                <VSelect
                  v-model="reasonFilter"
                  :items="reasonOptions"
                  :label="t('marketing.reason')"
                  density="compact"
                  hide-details
                />
              </VCol>
            </VRow>

            <VDataTable
              :headers="headers"
              :items="filteredUnsubscribes"
              :loading="store.loading"
              :items-per-page="25"
              hover
            >
              <template #item.email="{ item }">
                <strong>{{ item.email }}</strong>
              </template>

              <template #item.reason_label="{ item }">
                <VChip v-if="item.reason_label" size="small" label>
                  {{ item.reason_label }}
                </VChip>
                <span v-else class="text-medium-emphasis">-</span>
              </template>

              <template #item.feedback="{ item }">
                <span v-if="item.feedback" class="text-body-2">
                  {{ item.feedback.substring(0, 50) }}{{ item.feedback.length > 50 ? '...' : '' }}
                </span>
                <span v-else class="text-medium-emphasis">-</span>
              </template>

              <template #item.user="{ item }">
                <span v-if="item.user">{{ item.user.name }}</span>
                <span v-else class="text-medium-emphasis">-</span>
              </template>

              <template #item.campaign="{ item }">
                <span v-if="item.campaign">{{ item.campaign.name }}</span>
                <span v-else class="text-medium-emphasis">-</span>
              </template>

              <template #item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>

              <template #item.actions="{ item }">
                <VBtn
                  icon="mdi-email-check"
                  variant="text"
                  size="small"
                  color="success"
                  :title="t('marketing.resubscribe')"
                  @click="confirmResubscribe(item)"
                />
              </template>
            </VDataTable>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Resubscribe Confirmation Dialog -->
    <VDialog v-model="resubscribeDialog" max-width="400">
      <VCard>
        <VCardTitle>{{ t('marketing.confirm_resubscribe') }}</VCardTitle>
        <VCardText>
          {{ t('marketing.confirm_resubscribe_text', { email: emailToResubscribe }) }}
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn @click="resubscribeDialog = false">{{ t('common.cancel') }}</VBtn>
          <VBtn color="success" @click="resubscribe">{{ t('marketing.resubscribe') }}</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>
