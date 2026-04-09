<script setup lang="ts">
import { ref } from 'vue'

// Simulamos los datos que vendrían del TimerController de Tenanta
const metrics = ref({
  arrivalTime: '08:45 AM',
  leftTime: '18:15 PM',
  productiveTime: '6h 30m',
  desktimeTime: '8h 15m',
  timeAtWork: '9h 30m',
  effectiveness: '85%',
  productivity: '78%'
})

const period = ref('Hoy')

// Simulación de datos para el gráfico de barras por hora (8 AM a 6 PM)
const hourlyData = ref([
  { hour: '8 AM', productive: 20, neutral: 10, unproductive: 5 },
  { hour: '9 AM', productive: 50, neutral: 5, unproductive: 0 },
  { hour: '10 AM', productive: 45, neutral: 10, unproductive: 5 },
  { hour: '11 AM', productive: 60, neutral: 0, unproductive: 0 },
  { hour: '12 PM', productive: 30, neutral: 20, unproductive: 10 },
  { hour: '1 PM', productive: 10, neutral: 40, unproductive: 10 }, // Almuerzo
  { hour: '2 PM', productive: 55, neutral: 5, unproductive: 0 },
  { hour: '3 PM', productive: 50, neutral: 10, unproductive: 0 },
  { hour: '4 PM', productive: 40, neutral: 15, unproductive: 5 },
  { hour: '5 PM', productive: 35, neutral: 10, unproductive: 15 },
  { hour: '6 PM', productive: 15, neutral: 5, unproductive: 5 },
])
</script>

<template>
  <div class="desktime-dashboard bg-[#f5f6fa] min-h-screen font-sans text-[#333333]">
    
    <!-- Topbar -->
    <div class="bg-white px-8 py-4 flex justify-between items-center shadow-sm border-b border-gray-100">
      <h1 class="text-2xl font-semibold">Mi Productividad</h1>
      
      <div class="flex items-center space-x-4">
        <div class="flex bg-gray-100 rounded-lg p-1">
          <button class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors" :class="period === 'Día' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'" @click="period = 'Día'">Día</button>
          <button class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors" :class="period === 'Semana' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'" @click="period = 'Semana'">Semana</button>
          <button class="px-4 py-1.5 rounded-md text-sm font-medium transition-colors" :class="period === 'Mes' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'" @click="period = 'Mes'">Mes</button>
        </div>
        
        <div class="flex items-center bg-white border border-gray-200 rounded-lg px-3 py-1.5 cursor-pointer hover:bg-gray-50">
          <VIcon icon="mdi-chevron-left" size="20" class="text-gray-400 hover:text-gray-700" />
          <span class="mx-4 text-sm font-medium">Hoy, 9 de Abril</span>
          <VIcon icon="mdi-chevron-right" size="20" class="text-gray-400 hover:text-gray-700" />
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="p-8 max-w-7xl mx-auto space-y-6">
      
      <!-- KPIs Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Horarios -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
          <div class="flex justify-between items-start mb-2">
            <span class="text-sm font-medium text-gray-500">Hora de llegada</span>
            <VIcon icon="mdi-information-outline" size="16" class="text-gray-400 cursor-help" />
          </div>
          <div class="text-2xl font-bold text-gray-800 mb-4">{{ metrics.arrivalTime }}</div>
          
          <div class="flex justify-between items-start mb-2 pt-4 border-t border-gray-50">
            <span class="text-sm font-medium text-gray-500">Hora de salida</span>
          </div>
          <div class="text-2xl font-bold text-gray-800">{{ metrics.leftTime }}</div>
        </div>

        <!-- Card 2: Tiempos Productivos -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
          <div class="flex justify-between items-start mb-2">
            <span class="text-sm font-medium text-gray-500">Tiempo productivo</span>
            <VIcon icon="mdi-information-outline" size="16" class="text-gray-400 cursor-help" />
          </div>
          <div class="text-2xl font-bold text-[#4caf50] mb-4">{{ metrics.productiveTime }}</div>
          
          <div class="flex justify-between items-start mb-2 pt-4 border-t border-gray-50">
            <span class="text-sm font-medium text-gray-500">Tiempo total registrado</span>
          </div>
          <div class="text-2xl font-bold text-gray-800">{{ metrics.desktimeTime }}</div>
        </div>

        <!-- Card 3: Tiempo en el trabajo -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
          <div class="flex justify-between items-start mb-2">
            <span class="text-sm font-medium text-gray-500">Tiempo en el trabajo</span>
            <VIcon icon="mdi-information-outline" size="16" class="text-gray-400 cursor-help" />
          </div>
          <div class="text-2xl font-bold text-gray-800 mb-4">{{ metrics.timeAtWork }}</div>
          <div class="w-full bg-gray-100 rounded-full h-2 mb-1">
            <div class="bg-blue-500 h-2 rounded-full" style="width: 85%"></div>
          </div>
          <span class="text-xs text-gray-400 mt-2">85% de tu meta diaria (8h)</span>
        </div>

        <!-- Card 4: Efectividad -->
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between">
          <div class="flex justify-between items-start mb-2">
            <span class="text-sm font-medium text-gray-500">Productividad</span>
            <VIcon icon="mdi-information-outline" size="16" class="text-gray-400 cursor-help" />
          </div>
          <div class="text-3xl font-black text-gray-800 mb-2">{{ metrics.productivity }}</div>
          
          <div class="flex justify-between items-end mt-auto pt-4 border-t border-gray-50">
            <div>
              <span class="block text-sm font-medium text-gray-500 mb-1">Efectividad</span>
              <span class="text-xl font-bold text-[#4caf50]">{{ metrics.effectiveness }}</span>
            </div>
            <VIcon icon="mdi-trending-up" color="#4caf50" size="24" />
          </div>
        </div>

      </div>

      <!-- Gráfico de Productividad por Hora (Maquetación visual del concepto) -->
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-lg font-semibold">Actividad por hora</h2>
          <div class="flex space-x-4 text-xs font-medium">
            <span class="flex items-center"><div class="w-3 h-3 rounded-sm bg-[#4caf50] mr-2"></div> Productivo</span>
            <span class="flex items-center"><div class="w-3 h-3 rounded-sm bg-gray-300 mr-2"></div> Neutral</span>
            <span class="flex items-center"><div class="w-3 h-3 rounded-sm bg-[#ff9800] mr-2"></div> Improductivo</span>
          </div>
        </div>
        
        <div class="h-48 flex items-end justify-between px-2 pb-6 border-b border-gray-100 relative">
          <!-- Líneas guía horizontales simuladas -->
          <div class="absolute w-full h-full flex flex-col justify-between z-0 pointer-events-none opacity-10">
             <div class="border-t border-dashed border-gray-400 w-full"></div>
             <div class="border-t border-dashed border-gray-400 w-full"></div>
             <div class="border-t border-dashed border-gray-400 w-full"></div>
          </div>

          <!-- Barras apiladas -->
          <div v-for="data in hourlyData" :key="data.hour" class="flex flex-col justify-end w-12 h-full z-10 group relative">
            <!-- Tooltip en hover -->
            <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
              Total: {{ data.productive + data.neutral + data.unproductive }}m
            </div>
            
            <div class="w-full bg-[#ff9800] rounded-t-sm transition-all hover:brightness-110" :style="{ height: data.unproductive + '%' }"></div>
            <div class="w-full bg-gray-300 transition-all hover:brightness-95" :style="{ height: data.neutral + '%' }"></div>
            <div class="w-full bg-[#4caf50] rounded-b-sm transition-all hover:brightness-110" :style="{ height: data.productive + '%' }"></div>
            
            <span class="text-[10px] text-gray-400 absolute -bottom-6 left-1/2 transform -translate-x-1/2 font-medium">{{ data.hour }}</span>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
