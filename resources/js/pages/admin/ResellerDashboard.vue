<template>
  <div class="p-6">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-800">Panel del Distribuidor</h1>
      <p class="text-gray-600">Bienvenido de nuevo. Aquí tienes un resumen del estado de tus inquilinos.</p>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-full mr-4">
            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500 uppercase font-semibold">Inquilinos Activos</p>
            <h3 class="text-2xl font-bold text-gray-800">24</h3>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-full mr-4">
            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500 uppercase font-semibold">Comisiones Pendientes</p>
            <h3 class="text-2xl font-bold text-gray-800">$1,250.00</h3>
          </div>
        </div>
      </div>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Mis Inquilinos</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre del Inquilino</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Pago</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="tenant in tenants" :key="tenant.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ tenant.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tenant.plan }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="tenant.status === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ tenant.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ tenant.lastPayment }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:text-blue-900 cursor-pointer">Gestionar</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const tenants = ref([
  { id: 1, name: 'Inmobiliaria Norte', plan: 'Premium', status: 'Activo', lastPayment: '10/10/2023' },
  { id: 2, name: 'Estudio Jurídico Pérez', plan: 'Básico', status: 'Activo', lastPayment: '12/10/2023' },
  { id: 3, name: 'Consultora Global', plan: 'Enterprise', status: 'Inactivo', lastPayment: '05/09/2023' },
  { id: 4, name: 'Tech Solutions S.A.', plan: 'Premium', status: 'Activo', lastPayment: '15/10/2023' },
])
</script>
