<script setup lang="ts">
import { ref } from 'vue'

const comparison = ref({
  period1: 'Marzo 2026',
  period2: 'Abril 2026',
  diff: '+8.5%'
})
</script>

<template>
  <div class="reports-comparison p-8 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-8">
      <h1 class="text-2xl font-bold">Comparativo de Informes</h1>
      <div class="bg-white p-2 rounded-xl border flex space-x-2">
        <input type="date" class="text-xs border-none outline-none">
        <span class="text-gray-300">vs</span>
        <input type="date" class="text-xs border-none outline-none">
      </div>
    </div>

    <div class="bg-white p-8 rounded-3xl border shadow-sm">
      <div class="flex items-center justify-center space-x-20 mb-12">
        <div class="text-center">
          <p class="text-xs font-bold text-gray-400 uppercase mb-2">{{ comparison.period1 }}</p>
          <div class="text-5xl font-black text-gray-300">82%</div>
        </div>
        <div class="flex flex-col items-center">
          <div class="text-green-600 font-black text-xl">{{ comparison.diff }}</div>
          <VIcon icon="mdi-arrow-right-thick" color="green" size="32" />
        </div>
        <div class="text-center">
          <p class="text-xs font-bold text-gray-400 uppercase mb-2">{{ comparison.period2 }}</p>
          <div class="text-5xl font-black text-indigo-600">90.5%</div>
        </div>
      </div>
      
      <p class="text-center text-gray-500 text-sm leading-relaxed max-w-lg mx-auto">
        Tu equipo ha incrementado su efectividad operativa en un <strong>8.5%</strong> respecto al mes anterior. 
        Las aplicaciones productivas ganaron un 12% de cuota de tiempo.
      </p>
    </div>
  </div>
</template>
