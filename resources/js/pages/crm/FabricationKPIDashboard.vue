<script setup lang="ts">
import { ref } from 'vue'

const metrics = ref({
  units_produced: 1250,
  target: 1500,
  loss_rate: '2.4%',
  efficiency: '92%'
})
</script>

<template>
  <div class="fabrication-dashboard p-8 bg-slate-900 min-h-screen text-slate-200">
    <h1 class="text-xl font-bold mb-8 text-indigo-400">Indicadores de Producción / Unidades</h1>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div v-for="(val, label) in {
        'Unidades Producidas': metrics.units_produced,
        'Objetivo del Día': metrics.target,
        'Tasa de Merma': metrics.loss_rate,
        'Eficiencia OEE': metrics.efficiency
      }" :key="label" class="bg-slate-800 p-6 rounded-xl border border-slate-700">
        <p class="text-[10px] uppercase font-bold text-slate-500 mb-2">{{ label }}</p>
        <h3 class="text-3xl font-black text-white">{{ val }}</h3>
      </div>
    </div>
    <!-- Gráfico Circular Central (IMG_1887) -->
    <div class="mt-12 flex justify-center">
      <div class="w-64 h-64 rounded-full border-[15px] border-slate-700 flex flex-col items-center justify-center relative">
        <div class="absolute inset-0 rounded-full border-[15px] border-indigo-500 border-t-transparent border-r-transparent rotate-45"></div>
        <span class="text-4xl font-black text-white">83%</span>
        <span class="text-[10px] font-bold text-slate-500 uppercase">Cumplimiento</span>
      </div>
    </div>
  </div>
</template>
