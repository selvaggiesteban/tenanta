<script setup lang="ts">
import { ref } from 'vue'

const team = ref([
  { name: 'Esteban Selvaggi', productivity: 98, desktime: '8h 45m', score: 'Top 1' },
  { name: 'Lucía Mendoza', productivity: 92, desktime: '7h 30m', score: 'Top 2' },
  { name: 'Martín Gómez', productivity: 85, desktime: '8h 10m', score: 'Top 3' }
])
</script>

<template>
  <div class="team-management p-8 bg-[#f5f6fa] min-h-screen">
    <h1 class="text-2xl font-bold mb-8">Rendimiento de Equipos</h1>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Ranking (Screenshot 16 style) -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <h3 class="font-bold mb-6 flex items-center">
          <VIcon icon="mdi-trophy" color="orange" class="mr-2" /> Mejores Desempeños del Mes
        </h3>
        <div class="space-y-4">
          <div v-for="member in team" :key="member.name" class="flex items-center p-3 bg-gray-50 rounded-xl">
            <span class="text-xs font-black text-indigo-600 w-12">{{ member.score }}</span>
            <span class="flex-1 font-bold text-gray-800">{{ member.name }}</span>
            <span class="text-green-600 font-black">{{ member.productivity }}%</span>
          </div>
        </div>
      </div>

      <!-- Distribución por Dptos -->
      <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <h3 class="font-bold mb-6">Distribución de Horas por Departamento</h3>
        <div class="space-y-6">
          <div v-for="dept in ['Ventas', 'Soporte', 'Marketing']" :key="dept">
            <div class="flex justify-between text-xs font-bold uppercase text-gray-400 mb-2">
              <span>{{ dept }}</span>
              <span>{{ Math.floor(Math.random() * 500) }}h</span>
            </div>
            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
              <div class="h-full bg-indigo-500" :style="{ width: Math.random() * 100 + '%' }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
