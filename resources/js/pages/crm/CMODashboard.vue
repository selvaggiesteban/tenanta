<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const marketing = ref({
  roi_marketing: 0,
  cac: 0,
  ltv: 0,
  presupuesto_utilizado: 0,
  proyeccion_crecimiento: 0
})

onMounted(async () => {
  try {
    const response = await api.get('/dashboards/financials')
    marketing.value = response.data.data.cmo
  } catch (e) { console.error(e) }
})
</script>

<template>
  <div class="cmo-dashboard bg-[#0F172A] p-8 min-height-screen text-white font-sans">
    <div class="flex justify-between items-center mb-10">
      <div>
        <h1 class="text-3xl font-black tracking-tight">CMO Intelligence</h1>
        <p class="text-blue-400 font-medium">Marketing & ROI Dark Mode</p>
      </div>
      <VBtn color="indigo-accent-4" variant="elevated" prepend-icon="mdi-file-pdf-box">
        Exportar Estrategia
      </VBtn>
    </div>

    <!-- Bento Grid (IMG_1886 style) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <!-- Columna 1 -->
      <div class="space-y-6">
        <div class="bg-[#1E293B] p-6 rounded-2xl border border-gray-800">
          <p class="text-gray-400 text-xs font-bold uppercase mb-2">ROI Marketing</p>
          <div class="text-4xl font-black text-green-400">{{ marketing.roi_marketing }}%</div>
          <div class="mt-4 h-2 bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full bg-green-400" :style="{ width: marketing.roi_marketing + '%' }"></div>
          </div>
        </div>
        <div class="bg-[#1E293B] p-6 rounded-2xl border border-gray-800 flex items-center justify-between">
          <div>
            <p class="text-gray-400 text-xs font-bold uppercase">NPS Score</p>
            <div class="text-2xl font-bold">8.9</div>
          </div>
          <VProgressCircular :model-value="89" color="blue-lighten-3" size="60" width="8" />
        </div>
      </div>

      <!-- Columna 2 -->
      <div class="bg-[#1E293B] p-8 rounded-2xl border border-gray-800 flex flex-col justify-center text-center">
        <p class="text-gray-400 text-sm font-bold uppercase mb-4">Customer Lifetime Value (LTV)</p>
        <div class="text-6xl font-black text-white mb-2">{{ marketing.ltv }}</div>
        <p class="text-blue-400 font-bold italic">ARS / Cliente</p>
        <div class="mt-8 p-4 bg-[#064E3B] rounded-xl border border-green-900">
          <span class="text-green-400 font-bold uppercase text-xs">LTV:CAC Ratio</span>
          <div class="text-2xl font-black text-white">3.5x</div>
        </div>
      </div>

      <!-- Columna 3 -->
      <div class="space-y-6">
        <div class="bg-[#1E293B] p-6 rounded-2xl border border-gray-800">
          <p class="text-gray-400 text-xs font-bold uppercase mb-2">CAC (Costo Adquisición)</p>
          <div class="text-4xl font-black text-red-400">{{ marketing.cac }}</div>
          <p class="text-xs text-gray-500 mt-1">Promedio por nuevo cliente</p>
        </div>
        <div class="bg-[#1E293B] p-6 rounded-2xl border border-gray-800">
          <p class="text-gray-400 text-xs font-bold uppercase mb-4">Presupuesto Digital</p>
          <div class="flex items-end space-x-2 mb-4">
            <span class="text-3xl font-black">{{ marketing.presupuesto_utilizado }}</span>
            <span class="text-gray-500 text-sm pb-1">utilizado</span>
          </div>
          <div class="space-y-3">
            <div v-for="chan in ['Google Ads', 'Meta Ads', 'SEO']" :key="chan" class="flex items-center text-xs">
              <span class="flex-1 text-gray-400">{{ chan }}</span>
              <div class="w-24 h-1.5 bg-gray-700 rounded-full mr-2">
                <div class="h-full bg-blue-500" :style="{ width: Math.random() * 80 + '%' }"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
