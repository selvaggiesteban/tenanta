<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const salesStats = ref({
  ventas_cerradas: 0,
  cantidad_ventas: 0,
  en_proceso: 0,
  tasa_conversion: 0
})

const loading = ref(true)

const fetchSales = async () => {
  try {
    const response = await api.get('/dashboards/financials')
    salesStats.value = response.data.data.piblo
  } catch (error) {
    console.error('Error cargando ventas:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchSales)
</script>

<template>
  <div class="piblo-dashboard p-6 bg-[#F3F4F6] min-height-screen font-sans">
    <div class="bg-[#1E3A8A] text-white p-6 rounded-t-2xl flex justify-between items-center">
      <h1 class="text-2xl font-bold tracking-tight">Ventas Piblo</h1>
      <div class="flex space-x-2">
        <span class="bg-white text-[#1E3A8A] px-4 py-1 rounded-full text-xs font-bold">Días Hábiles: 22</span>
        <span class="bg-white text-[#1E3A8A] px-4 py-1 rounded-full text-xs font-bold">Restantes: 5</span>
      </div>
    </div>

    <!-- KPIs de Ventas (IMG_1885 style) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
      <div v-for="(val, label) in {
        'Ventas Cerradas': salesStats.ventas_cerradas,
        'Oportunidades': salesStats.cantidad_ventas,
        'En Negociación': salesStats.en_proceso,
        'Conversión': salesStats.tasa_conversion + '%'
      }" :key="label" class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-[#1E3A8A]">
        <p class="text-xs text-gray-500 font-bold uppercase mb-1">{{ label }}</p>
        <h3 class="text-2xl font-extrabold text-gray-800">{{ val }}</h3>
      </div>
    </div>

    <!-- Visualización de Embudo y Canales -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
      <div class="bg-white p-8 rounded-2xl shadow-sm relative overflow-hidden">
        <h3 class="text-lg font-bold mb-6">Embudo por Región</h3>
        <!-- Representación del Funnel (IMG_1885) -->
        <div class="flex flex-col items-center space-y-2">
          <div class="w-full bg-[#1E3A8A] h-12 rounded-lg flex items-center justify-center text-white text-xs font-bold">Norte - 45%</div>
          <div class="w-4/5 bg-[#2563EB] h-12 rounded-lg flex items-center justify-center text-white text-xs font-bold">Centro - 30%</div>
          <div class="w-3/5 bg-[#3B82F6] h-12 rounded-lg flex items-center justify-center text-white text-xs font-bold">Sur - 25%</div>
        </div>
      </div>

      <div class="bg-white p-8 rounded-2xl shadow-sm">
        <h3 class="text-lg font-bold mb-6">Ventas por Canales</h3>
        <div class="flex justify-center items-center h-48">
          <div class="w-40 h-48 bg-[#FDE047] rounded-full border-[20px] border-[#1E3A8A] flex items-center justify-center">
            <span class="text-xl font-black">100%</span>
          </div>
        </div>
        <div class="flex justify-center space-x-4 mt-4">
          <span class="flex items-center text-xs font-bold text-gray-600">
            <div class="w-3 h-3 bg-[#1E3A8A] mr-1 rounded-full"></div> Directo
          </span>
          <span class="flex items-center text-xs font-bold text-gray-600">
            <div class="w-3 h-3 bg-[#FDE047] mr-1 rounded-full"></div> Referidos
          </span>
        </div>
      </div>
    </div>
  </div>
</template>
