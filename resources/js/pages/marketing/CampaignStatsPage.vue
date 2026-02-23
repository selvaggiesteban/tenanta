<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const store = useMarketingStore()

const campaignId = computed(() => Number(route.params.id))

const statCards = computed(() => {
  const stats = store.currentCampaignStats
  if (!stats) return []

  return [
    {
      title: t('marketing.sent'),
      value: stats.overview.sent_count,
      icon: 'mdi-send',
      color: 'primary',
    },
    {
      title: t('marketing.delivered'),
      value: stats.overview.delivered_count,
      rate: stats.rates.delivery_rate,
      icon: 'mdi-email-check',
      color: 'success',
    },
    {
      title: t('marketing.opened'),
      value: stats.overview.opened_count,
      rate: stats.rates.open_rate,
      icon: 'mdi-email-open',
      color: 'info',
    },
    {
      title: t('marketing.clicked'),
      value: stats.overview.clicked_count,
      rate: stats.rates.click_rate,
      icon: 'mdi-cursor-default-click',
      color: 'warning',
    },
    {
      title: t('marketing.bounced'),
      value: stats.overview.bounced_count,
      rate: stats.rates.bounce_rate,
      icon: 'mdi-email-alert',
      color: 'error',
    },
    {
      title: t('marketing.unsubscribed'),
      value: stats.overview.unsubscribed_count,
      rate: stats.rates.unsubscribe_rate,
      icon: 'mdi-email-off',
      color: 'grey',
    },
  ]
})

function formatRate(rate: number): string {
  return `${rate.toFixed(1)}%`
}

function formatNumber(num: number): string {
  return num.toLocaleString()
}

function goBack() {
  router.push({ name: 'marketing-campaigns' })
}

onMounted(async () => {
  await store.fetchCampaign(campaignId.value)
  await store.fetchCampaignStats(campaignId.value)
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
          {{ t('marketing.back_to_campaigns') }}
        </VBtn>
      </VCol>
    </VRow>

    <VRow v-if="store.currentCampaign">
      <VCol cols="12">
        <VCard>
          <VCardTitle>
            {{ store.currentCampaign.name }}
          </VCardTitle>
          <VCardSubtitle>
            {{ store.currentCampaign.subject }}
          </VCardSubtitle>
        </VCard>
      </VCol>
    </VRow>

    <!-- Stat Cards -->
    <VRow>
      <VCol
        v-for="stat in statCards"
        :key="stat.title"
        cols="12"
        sm="6"
        md="4"
        lg="2"
      >
        <VCard>
          <VCardText class="text-center">
            <VAvatar :color="stat.color" variant="tonal" size="48" class="mb-2">
              <VIcon :icon="stat.icon" />
            </VAvatar>
            <div class="text-h5 font-weight-bold">
              {{ formatNumber(stat.value) }}
            </div>
            <div v-if="stat.rate !== undefined" class="text-body-2 text-medium-emphasis">
              {{ formatRate(stat.rate) }}
            </div>
            <div class="text-caption">{{ stat.title }}</div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <VRow v-if="store.currentCampaignStats">
      <!-- Engagement Stats -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardTitle>{{ t('marketing.engagement') }}</VCardTitle>
          <VCardText>
            <VList density="compact">
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-eye" />
                </template>
                <VListItemTitle>{{ t('marketing.total_opens') }}</VListItemTitle>
                <template #append>
                  <strong>{{ formatNumber(store.currentCampaignStats.engagement.total_opens) }}</strong>
                </template>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-cursor-default-click" />
                </template>
                <VListItemTitle>{{ t('marketing.total_clicks') }}</VListItemTitle>
                <template #append>
                  <strong>{{ formatNumber(store.currentCampaignStats.engagement.total_clicks) }}</strong>
                </template>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-account-eye" />
                </template>
                <VListItemTitle>{{ t('marketing.unique_opens') }}</VListItemTitle>
                <template #append>
                  <strong>{{ formatNumber(store.currentCampaignStats.engagement.unique_opens) }}</strong>
                </template>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-account-star" />
                </template>
                <VListItemTitle>{{ t('marketing.unique_clicks') }}</VListItemTitle>
                <template #append>
                  <strong>{{ formatNumber(store.currentCampaignStats.engagement.unique_clicks) }}</strong>
                </template>
              </VListItem>
              <VListItem>
                <template #prepend>
                  <VIcon icon="mdi-chart-line" />
                </template>
                <VListItemTitle>{{ t('marketing.click_to_open_rate') }}</VListItemTitle>
                <template #append>
                  <strong>{{ formatRate(store.currentCampaignStats.rates.click_to_open_rate) }}</strong>
                </template>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Device Stats -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardTitle>{{ t('marketing.devices') }}</VCardTitle>
          <VCardText>
            <VList density="compact">
              <VListItem
                v-for="(count, device) in store.currentCampaignStats.device_stats"
                :key="device"
              >
                <template #prepend>
                  <VIcon
                    :icon="device === 'mobile' ? 'mdi-cellphone' : device === 'tablet' ? 'mdi-tablet' : 'mdi-monitor'"
                  />
                </template>
                <VListItemTitle class="text-capitalize">{{ device }}</VListItemTitle>
                <template #append>
                  <strong>{{ formatNumber(count) }}</strong>
                </template>
              </VListItem>
              <VListItem v-if="Object.keys(store.currentCampaignStats.device_stats).length === 0">
                <VListItemTitle class="text-medium-emphasis">
                  {{ t('marketing.no_device_data') }}
                </VListItemTitle>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Top Links -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardTitle>{{ t('marketing.top_links') }}</VCardTitle>
          <VCardText>
            <VList density="compact">
              <VListItem
                v-for="link in store.currentCampaignStats.top_links.slice(0, 5)"
                :key="link.url"
              >
                <VListItemTitle class="text-truncate" :title="link.url">
                  {{ link.url }}
                </VListItemTitle>
                <VListItemSubtitle>
                  {{ formatNumber(link.click_count) }} {{ t('marketing.clicks') }}
                  ({{ formatNumber(link.unique_clicks) }} {{ t('marketing.unique') }})
                </VListItemSubtitle>
              </VListItem>
              <VListItem v-if="store.currentCampaignStats.top_links.length === 0">
                <VListItemTitle class="text-medium-emphasis">
                  {{ t('marketing.no_link_data') }}
                </VListItemTitle>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>

      <!-- Geographic Stats -->
      <VCol cols="12" md="6">
        <VCard>
          <VCardTitle>{{ t('marketing.locations') }}</VCardTitle>
          <VCardText>
            <VList density="compact">
              <VListItem
                v-for="(count, country) in store.currentCampaignStats.geo_stats"
                :key="country"
              >
                <VListItemTitle>{{ country }}</VListItemTitle>
                <template #append>
                  <strong>{{ formatNumber(count) }}</strong>
                </template>
              </VListItem>
              <VListItem v-if="Object.keys(store.currentCampaignStats.geo_stats).length === 0">
                <VListItemTitle class="text-medium-emphasis">
                  {{ t('marketing.no_geo_data') }}
                </VListItemTitle>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Loading State -->
    <VRow v-if="store.loading">
      <VCol cols="12" class="text-center">
        <VProgressCircular indeterminate color="primary" />
      </VCol>
    </VRow>
  </VContainer>
</template>
