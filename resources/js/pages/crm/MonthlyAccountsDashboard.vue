<script setup lang="ts">
import { ref } from 'vue'

const accounts = ref([
  { name: 'Inmobiliaria Norte', total: 450000, status: 'Pagado', growth: '+12%' },
  { name: 'Clínica Dental Sur', total: 120000, status: 'Pendiente', growth: '+5%' },
  { name: 'Estudio Jurídico', total: 890000, status: 'Vencido', growth: '+20%' }
])
</script>

<template>
  <div class="accounts-dashboard p-8 bg-white min-h-screen">
    <div class="flex justify-between items-end mb-10 border-b pb-6">
      <h1 class="text-3xl font-black text-gray-900 tracking-tighter">Cuentas Mensuales</h1>
      <div class="text-right">
        <p class="text-xs font-bold text-gray-400 uppercase">Cobro Diario</p>
        <p class="text-2xl font-black text-indigo-600">$145.000</p>
      </div>
    </div>
    
    <div class="grid grid-cols-1 gap-4">
      <div v-for="acc in accounts" :key="acc.name" class="flex items-center p-4 border rounded-xl hover:bg-gray-50 transition-colors">
        <div class="w-12 h-12 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600 font-bold mr-4">
          {{ acc.name.charAt(0) }}
        </div>
        <div class="flex-1">
          <h4 class="font-bold text-gray-800">{{ acc.name }}</h4>
          <span class="text-xs text-green-600 font-bold">{{ acc.growth }} vs mes anterior</span>
        </div>
        <div class="text-right mr-8">
          <p class="text-sm font-black text-gray-900">${{ acc.total.toLocaleString() }}</p>
          <p class="text-[10px] uppercase font-bold text-gray-400">Total Facturado</p>
        </div>
        <div :class="acc.status === 'Pagado' ? 'text-green-600' : 'text-orange-600'" class="font-bold text-xs">
          ● {{ acc.status }}
        </div>
      </div>
    </div>
  </div>
</template>
