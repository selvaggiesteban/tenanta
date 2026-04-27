<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const loading = ref(true)
const stats = ref({
  total_messages: 0,
  avg_response_time: '0m',
  volume_by_channel: [] as { channel: string, count: number }[],
  messages_over_time: [] as { date: string, count: number }[]
})

const volumeChartOptions = ref({
  chart: {
    type: 'donut',
  },
  labels: [] as string[],
  colors: ['#4CAF50', '#2196F3', '#E91E63', '#FF9800', '#9C27B0'],
  legend: {
    position: 'bottom'
  },
  responsive: [{
    breakpoint: 480,
    options: {
      chart: {
        width: 200
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
})

const volumeChartSeries = ref([] as number[])

const historyChartOptions = ref({
  chart: {
    type: 'area',
    height: 350,
    zoom: {
      enabled: false
    }
  },
  dataLabels: {
    enabled: false
  },
  stroke: {
    curve: 'smooth'
  },
  xaxis: {
    categories: [] as string[],
  },
  yaxis: {
    title: {
      text: 'Mensajes'
    }
  },
  colors: ['#2196F3']
})

const historyChartSeries = ref([
  {
    name: "Mensajes",
    data: [] as number[]
  }
])

async function fetchAnalytics() {
  loading.value = true
  try {
    const response = await api.get('/omnichannel/analytics')
    const data = response.data.data
    
    stats.value = data
    
    // Update Volume Chart
    volumeChartOptions.value.labels = data.volume_by_channel.map((item: any) => item.channel)
    volumeChartSeries.value = data.volume_by_channel.map((item: any) => item.count)
    
    // Update History Chart
    historyChartOptions.value.xaxis.categories = data.messages_over_time.map((item: any) => item.date)
    historyChartSeries.value[0].data = data.messages_over_time.map((item: any) => item.count)
    
  } catch (e) {
    console.error('Error fetching omnichannel analytics:', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAnalytics()
})
</script>

<template>
  <VContainer fluid>
    <div class="d-flex align-center mb-6">
      <div>
        <h1 class="text-h4 font-weight-bold">Analítica Omnicanal</h1>
        <p class="text-subtitle-1 text-grey">Métricas de rendimiento de todos tus canales</p>
      </div>
      <VSpacer />
      <VBtn icon="mdi-refresh" variant="text" @click="fetchAnalytics" :loading="loading" />
    </div>

    <VRow v-if="loading" class="mt-12">
      <VCol cols="12" class="text-center">
        <VProgressCircular indeterminate color="primary" size="64" />
        <p class="mt-4 text-grey">Cargando estadísticas...</p>
      </VCol>
    </VRow>

    <template v-else>
      <!-- Summary Cards -->
      <VRow>
        <VCol cols="12" md="4">
          <VCard elevation="2" rounded="lg">
            <VCardText class="d-flex align-center pa-6">
              <VAvatar color="primary-lighten-4" size="56" class="mr-4">
                <VIcon icon="mdi-message-text-outline" color="primary" size="32" />
              </VAvatar>
              <div>
                <div class="text-overline mb-1">Total Mensajes</div>
                <div class="text-h4 font-weight-bold">{{ stats.total_messages }}</div>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="4">
          <VCard elevation="2" rounded="lg">
            <VCardText class="d-flex align-center pa-6">
              <VAvatar color="success-lighten-4" size="56" class="mr-4">
                <VIcon icon="mdi-clock-outline" color="success" size="32" />
              </VAvatar>
              <div>
                <div class="text-overline mb-1">Tiempo de Primera Respuesta</div>
                <div class="text-h4 font-weight-bold">{{ stats.avg_response_time }}</div>
              </div>
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="4">
          <VCard elevation="2" rounded="lg">
            <VCardText class="d-flex align-center pa-6">
              <VAvatar color="info-lighten-4" size="56" class="mr-4">
                <VIcon icon="mdi-account-group-outline" color="info" size="32" />
              </VAvatar>
              <div>
                <div class="text-overline mb-1">Agentes Activos</div>
                <div class="text-h4 font-weight-bold">12</div>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>

      <!-- Charts Row -->
      <VRow class="mt-4">
        <VCol cols="12" lg="8">
          <VCard elevation="2" rounded="lg">
            <VCardTitle class="pa-6 pb-0">Volumen de Mensajes (Últimos 30 días)</VCardTitle>
            <VCardText class="pa-6">
              <apexchart 
                type="area" 
                height="350" 
                :options="historyChartOptions" 
                :series="historyChartSeries" 
              />
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" lg="4">
          <VCard elevation="2" rounded="lg" class="h-100">
            <VCardTitle class="pa-6 pb-0">Distribución por Canal</VCardTitle>
            <VCardText class="pa-6 d-flex flex-column justify-center align-center">
              <apexchart 
                type="donut" 
                width="320" 
                :options="volumeChartOptions" 
                :series="volumeChartSeries" 
              />
              
              <VList class="w-100 mt-4" density="compact">
                <VListItem v-for="(item, i) in stats.volume_by_channel" :key="i">
                  <template #prepend>
                    <VIcon 
                      :icon="getChannelIcon(item.channel)" 
                      :color="volumeChartOptions.colors[i]" 
                      class="mr-2"
                    />
                  </template>
                  <VListItemTitle>{{ item.channel }}</VListItemTitle>
                  <template #append>
                    <span class="font-weight-bold">{{ item.count }}</span>
                  </template>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>
  </VContainer>
</template>

<script lang="ts">
// Help function for icons
function getChannelIcon(type: string) {
  const icons: any = { 
    whatsapp: 'mdi-whatsapp', 
    messenger: 'mdi-facebook-messenger', 
    instagram: 'mdi-instagram',
    telegram: 'mdi-send',
    email: 'mdi-email'
  }
  return icons[type.toLowerCase()] || 'mdi-chat'
}
</script>

<style scoped>
.v-card {
  transition: transform 0.2s;
}
.v-card:hover {
  transform: translateY(-4px);
}
</style>
