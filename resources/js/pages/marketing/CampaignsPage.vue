<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'
import type { CampaignListItem, CampaignStatus } from '@/types/marketing'

const { t } = useI18n()
const router = useRouter()
const store = useMarketingStore()

const search = ref('')
const statusFilter = ref<CampaignStatus | ''>('')
const deleteDialog = ref(false)
const campaignToDelete = ref<CampaignListItem | null>(null)

const statusOptions = [
  { title: t('marketing.all_statuses'), value: '' },
  { title: t('marketing.status.draft'), value: 'draft' },
  { title: t('marketing.status.scheduled'), value: 'scheduled' },
  { title: t('marketing.status.sending'), value: 'sending' },
  { title: t('marketing.status.sent'), value: 'sent' },
]

const headers = [
  { title: t('marketing.campaign_name'), key: 'name', sortable: true },
  { title: t('marketing.status'), key: 'status', sortable: true },
  { title: t('marketing.recipients'), key: 'recipient_count', sortable: true },
  { title: t('marketing.open_rate'), key: 'open_rate', sortable: true },
  { title: t('marketing.click_rate'), key: 'click_rate', sortable: true },
  { title: t('common.created_at'), key: 'created_at', sortable: true },
  { title: t('common.actions'), key: 'actions', sortable: false, align: 'end' },
]

const filteredCampaigns = computed(() => {
  let result = store.campaigns
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    result = result.filter(c =>
      c.name.toLowerCase().includes(searchLower) ||
      c.subject.toLowerCase().includes(searchLower)
    )
  }
  if (statusFilter.value) {
    result = result.filter(c => c.status === statusFilter.value)
  }
  return result
})

function getStatusColor(status: CampaignStatus): string {
  const colors: Record<CampaignStatus, string> = {
    draft: 'grey',
    scheduled: 'info',
    sending: 'warning',
    sent: 'success',
    paused: 'warning',
    cancelled: 'error',
  }
  return colors[status] || 'grey'
}

function formatRate(rate: number | null): string {
  if (rate === null) return '-'
  return `${rate.toFixed(1)}%`
}

function formatDate(date: string | null): string {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

function goToCreate() {
  router.push({ name: 'marketing-campaign-create' })
}

function goToEdit(campaign: CampaignListItem) {
  router.push({ name: 'marketing-campaign-edit', params: { id: campaign.id } })
}

function goToStats(campaign: CampaignListItem) {
  router.push({ name: 'marketing-campaign-stats', params: { id: campaign.id } })
}

async function duplicateCampaign(campaign: CampaignListItem) {
  try {
    const newCampaign = await store.duplicateCampaign(campaign.id)
    router.push({ name: 'marketing-campaign-edit', params: { id: newCampaign.id } })
  } catch (e) {
    // Error handled by store
  }
}

function confirmDelete(campaign: CampaignListItem) {
  campaignToDelete.value = campaign
  deleteDialog.value = true
}

async function deleteCampaign() {
  if (!campaignToDelete.value) return
  try {
    await store.deleteCampaign(campaignToDelete.value.id)
    deleteDialog.value = false
    campaignToDelete.value = null
  } catch (e) {
    // Error handled by store
  }
}

onMounted(() => {
  store.fetchCampaigns()
})
</script>

<template>
  <VContainer fluid>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex align-center">
            <VIcon icon="mdi-email-multiple" class="mr-2" />
            {{ t('marketing.campaigns') }}
            <VSpacer />
            <VBtn
              color="primary"
              prepend-icon="mdi-plus"
              @click="goToCreate"
            >
              {{ t('marketing.new_campaign') }}
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
              :items="filteredCampaigns"
              :loading="store.loading"
              :items-per-page="15"
              hover
            >
              <template #item.name="{ item }">
                <div>
                  <strong>{{ item.name }}</strong>
                  <div class="text-caption text-medium-emphasis">
                    {{ item.subject }}
                  </div>
                </div>
              </template>

              <template #item.status="{ item }">
                <VChip
                  :color="getStatusColor(item.status)"
                  size="small"
                  label
                >
                  {{ t(`marketing.status.${item.status}`) }}
                </VChip>
              </template>

              <template #item.recipient_count="{ item }">
                {{ item.recipient_count.toLocaleString() }}
              </template>

              <template #item.open_rate="{ item }">
                {{ formatRate(item.open_rate) }}
              </template>

              <template #item.click_rate="{ item }">
                {{ formatRate(item.click_rate) }}
              </template>

              <template #item.created_at="{ item }">
                {{ formatDate(item.created_at) }}
              </template>

              <template #item.actions="{ item }">
                <VBtn
                  v-if="item.status === 'sent'"
                  icon="mdi-chart-bar"
                  variant="text"
                  size="small"
                  :title="t('marketing.view_stats')"
                  @click="goToStats(item)"
                />
                <VBtn
                  v-if="item.status === 'draft'"
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
                  @click="duplicateCampaign(item)"
                />
                <VBtn
                  v-if="['draft', 'scheduled'].includes(item.status)"
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
          {{ t('marketing.confirm_delete_campaign', { name: campaignToDelete?.name }) }}
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn @click="deleteDialog = false">{{ t('common.cancel') }}</VBtn>
          <VBtn color="error" @click="deleteCampaign">{{ t('common.delete') }}</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>
