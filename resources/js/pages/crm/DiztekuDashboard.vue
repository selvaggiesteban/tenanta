<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'

const stats = ref({
  total_ingresos: 0,
  total_gastos: 0,
  beneficio_neto: 0,
  margen_operativo: 0,
  moneda: 'ARS'
})

const monthlyData = ref([])
const loading = ref(true)

const fetchFinancials = async () => {
  try {
    const response = await api.get('/dashboards/financials')
    stats.value = response.data.data.dizteku
    monthlyData.value = response.data.data.cuentas_mensuales
  } catch (error) {
    console.error('Error cargando finanzas:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchFinancials)

const formatMoney = (val: number) => {
  return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(val)
}
</script>

<template>
  <div class="dizteku-dashboard p-6 bg-[#ECFDF5] min-height-screen">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-3xl font-bold text-[#065f46]">Dashboard Financiero Dizteku</h1>
      <button class="bg-[#10B981] text-white px-6 py-2 rounded-lg font-bold shadow-md hover:bg-[#059669]">
        📥 Exportar Reporte PDF
      </button>
    </div>

    <!-- KPIs Superiores -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-[#10B981] p-6 rounded-xl text-white shadow-lg">
        <p class="text-sm uppercase font-semibold opacity-80">Total Ingresos</p>
        <h2 class="text-3xl font-bold">{{ formatMoney(stats.total_ingresos) }}</h2>
      </div>
      <div class="bg-[#34D399] p-6 rounded-xl text-white shadow-lg">
        <p class="text-sm uppercase font-semibold opacity-80">Total Gastos</p>
        <h2 class="text-3xl font-bold">{{ formatMoney(stats.total_gastos) }}</h2>
      </div>
      <div class="bg-[#059669] p-6 rounded-xl text-white shadow-lg">
        <p class="text-sm uppercase font-semibold opacity-80">Beneficio Neto</p>
        <h2 class="text-3xl font-bold">{{ formatMoney(stats.beneficio_neto) }}</h2>
      </div>
      <div class="bg-[#064E3B] p-6 rounded-xl text-white shadow-lg">
        <p class="text-sm uppercase font-semibold opacity-80">% Margen</p>
        <h2 class="text-3xl font-bold">{{ stats.margen_operativo }}%</h2>
      </div>
    </div>

    <!-- Gráfico y Tabla -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-md">
        <h3 class="text-lg font-bold mb-4">Evolución de Beneficios</h3>
        <div class="h-64 flex items-end justify-around px-4">
          <!-- Simulación de Barras Verdes (IMG_1884 style) -->
          <div v-for="i in 12" :key="i" 
               class="w-8 bg-[#10B981] rounded-t-sm" 
               :style="{ height: Math.random() * 100 + '%' }"></div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow-md">
        <h3 class="text-lg font-bold mb-4">Top por Segmento</h3>
        <div class="space-y-4">
          <div v-for="seg in ['Gobierno', 'Pyme', 'Socios']" :key="seg" class="flex items-center">
            <span class="flex-1 font-medium">{{ seg }}</span>
            <span class="text-[#10B981] font-bold">70%</span>
            <VIcon icon="mdi-rhombus" color="#10B981" class="ml-2" size="14" />
          </div>
        </div>
      </div>
    </div>

    <!-- Tabla Detallada -->
    <div class="mt-8 bg-white rounded-2xl shadow-md overflow-hidden">
      <table class="w-full text-left">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="p-4">Año</th>
            <th class="p-4">Concepto</th>
            <th class="p-4 text-right">Monto</th>
            <th class="p-4 text-center">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in monthlyData" :key="item.accepted_at" class="border-b hover:bg-gray-50">
            <td class="p-4">2026</td>
            <td class="p-4 font-medium">{{ item.title }}</td>
            <td class="p-4 text-right font-bold">{{ formatMoney(item.total) }}</td>
            <td class="p-4 text-center">
              <VIcon icon="mdi-rhombus" color="#10B981" size="18" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
