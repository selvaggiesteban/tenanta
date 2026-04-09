<script setup lang="ts">
import { ref } from 'vue'

const reports = [
  { name: 'Tiempo de Trabajo por Empleado', type: 'CSV/PDF', last: 'Hoy 10:00' },
  { name: 'Uso de Aplicaciones Improductivas', type: 'Excel', last: 'Ayer' },
  { name: 'Comparativo de Productividad Q1 vs Q2', type: 'PDF', last: '01/04/2026' }
]
</script>

<template>
  <div class="exports-bi p-8 bg-gray-50 min-h-screen">
    <div class="max-w-4xl mx-auto">
      <div class="text-center mb-12">
        <VIcon icon="mdi-database-export" size="64" color="indigo" class="mb-4" />
        <h1 class="text-3xl font-black text-gray-900">Exportaciones y Business Intelligence</h1>
        <p class="text-gray-500">Genera plantillas de datos para análisis externo.</p>
      </div>

      <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
        <div v-for="rep in reports" :key="acc" class="p-6 border-b flex items-center hover:bg-gray-50 cursor-pointer">
          <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mr-6">
            <VIcon icon="mdi-file-chart" size="24" />
          </div>
          <div class="flex-1">
            <h4 class="font-bold text-gray-800">{{ rep.name }}</h4>
            <p class="text-xs text-gray-400">Formato: {{ rep.type }} | Último: {{ rep.last }}</p>
          </div>
          <button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-600 hover:text-white transition-all">
            Nueva Exportación
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
