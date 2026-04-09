<script setup lang="ts">
import { ref } from 'vue'

const projects = ref([
  { id: 1, name: 'Rediseño Web Tenanta', progress: 75, status: 'En Proceso', lead: 'Esteban S.' },
  { id: 2, name: 'Integración Meta API', progress: 40, status: 'Retrasado', lead: 'Lucía M.' },
  { id: 3, name: 'Carga Masiva XLSX', progress: 100, status: 'Completado', lead: 'Martín G.' }
])
</script>

<template>
  <div class="project-dashboard p-8 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold mb-8">Gestión de Proyectos y Carga</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div v-for="project in projects" :key="project.id" class="bg-white p-6 rounded-2xl shadow-sm border">
        <div class="flex justify-between items-start mb-4">
          <h3 class="font-bold text-gray-800">{{ project.name }}</h3>
          <span :class="project.status === 'Completado' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'" 
                class="text-[10px] uppercase font-black px-2 py-1 rounded">
            {{ project.status }}
          </span>
        </div>
        <div class="mb-4">
          <div class="flex justify-between text-xs mb-1">
            <span>Progreso</span>
            <span>{{ project.progress }}%</span>
          </div>
          <div class="w-full bg-gray-100 h-2 rounded-full">
            <div class="bg-indigo-600 h-2 rounded-full" :style="{ width: project.progress + '%' }"></div>
          </div>
        </div>
        <p class="text-xs text-gray-500">Responsable: <strong>{{ project.lead }}</strong></p>
      </div>
    </div>
  </div>
</template>
