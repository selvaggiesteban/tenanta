<script setup lang="ts">
import { ref } from 'vue'

const weekDays = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']
const currentWeek = 'Abril 6 - Abril 12, 2026'

const teamSchedules = ref([
  { 
    id: 1, 
    name: 'Esteban Selvaggi', 
    role: 'Admin', 
    avatar: 'ES',
    days: [
      { type: 'work', time: '09:00 - 18:00', label: 'Oficina' },
      { type: 'work', time: '09:00 - 18:00', label: 'Oficina' },
      { type: 'remote', time: '09:00 - 18:00', label: 'Home Office' },
      { type: 'work', time: '09:00 - 18:00', label: 'Oficina' },
      { type: 'work', time: '09:00 - 18:00', label: 'Oficina' },
      { type: 'off', label: 'Libre' },
      { type: 'off', label: 'Libre' }
    ]
  },
  { 
    id: 2, 
    name: 'Lucía Mendoza', 
    role: 'Manager', 
    avatar: 'LM',
    days: [
      { type: 'work', time: '08:00 - 17:00', label: 'Oficina' },
      { type: 'vacation', label: 'Vacaciones' },
      { type: 'vacation', label: 'Vacaciones' },
      { type: 'vacation', label: 'Vacaciones' },
      { type: 'vacation', label: 'Vacaciones' },
      { type: 'off', label: 'Libre' },
      { type: 'off', label: 'Libre' }
    ]
  },
  { 
    id: 3, 
    name: 'Martín Gómez', 
    role: 'Member', 
    avatar: 'MG',
    days: [
      { type: 'work', time: '10:00 - 19:00', label: 'Oficina' },
      { type: 'work', time: '10:00 - 19:00', label: 'Oficina' },
      { type: 'work', time: '10:00 - 19:00', label: 'Oficina' },
      { type: 'sick', label: 'Médico' },
      { type: 'work', time: '10:00 - 19:00', label: 'Oficina' },
      { type: 'off', label: 'Libre' },
      { type: 'off', label: 'Libre' }
    ]
  }
])

const getDayStyle = (type: string) => {
  switch(type) {
    case 'work': return 'bg-green-50 border-green-200 text-green-700'
    case 'remote': return 'bg-blue-50 border-blue-200 text-blue-700'
    case 'vacation': return 'bg-orange-50 border-orange-200 text-orange-700'
    case 'sick': return 'bg-red-50 border-red-200 text-red-700'
    default: return 'bg-gray-50 border-gray-100 text-gray-400'
  }
}
</script>

<template>
  <div class="work-schedules bg-[#f5f6fa] min-h-screen p-8 font-sans">
    
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Horarios de Trabajo</h1>
        <p class="text-gray-500 text-sm">Gestiona turnos y disponibilidad del equipo</p>
      </div>
      <div class="flex space-x-3">
        <button class="bg-white border border-gray-200 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
          Importar
        </button>
        <button class="bg-[#4caf50] text-white px-6 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-[#43a047] transition-colors flex items-center">
          <VIcon icon="mdi-plus" size="18" class="mr-2" />
          Añadir Horario
        </button>
      </div>
    </div>

    <!-- Banner Informativo (Screenshot 10) -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-blue-100 flex items-center mb-8 relative overflow-hidden">
      <div class="absolute right-0 top-0 opacity-10">
        <VIcon icon="mdi-calendar-clock" size="120" />
      </div>
      <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mr-6 text-blue-500">
        <VIcon icon="mdi-lightbulb-on-outline" size="32" />
      </div>
      <div class="flex-1">
        <h3 class="text-lg font-bold text-gray-800 mb-1">¡Optimiza la planificación de tu equipo!</h3>
        <p class="text-gray-600 text-sm max-w-2xl">
          Define turnos recurrentes, marca ausencias médicas o vacaciones y visualiza de un vistazo quién está en la oficina, quién trabaja remoto y quién está fuera.
        </p>
      </div>
      <button class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-200 transition-colors ml-6">
        Entendido
      </button>
    </div>

    <!-- Calendar Grid Control -->
    <div class="bg-white rounded-t-2xl border-x border-t border-gray-200 p-4 flex justify-between items-center">
      <div class="flex items-center space-x-4">
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
          <VIcon icon="mdi-chevron-left" />
        </button>
        <span class="text-lg font-bold text-gray-700">{{ currentWeek }}</span>
        <button class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
          <VIcon icon="mdi-chevron-right" />
        </button>
      </div>
      <div class="flex bg-gray-100 rounded-lg p-1">
        <button class="px-4 py-1.5 rounded-md text-sm font-medium bg-white shadow-sm">Semana</button>
        <button class="px-4 py-1.5 rounded-md text-sm font-medium text-gray-500 hover:text-gray-700">Mes</button>
      </div>
    </div>

    <!-- Main Grid (Screenshot 13 style) -->
    <div class="bg-white rounded-b-2xl shadow-sm border border-gray-200 overflow-x-auto">
      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-200">
            <th class="p-4 text-left text-xs font-bold text-gray-400 uppercase tracking-wider min-w-[200px]">Miembro del equipo</th>
            <th v-for="day in weekDays" :key="day" class="p-4 text-center text-xs font-bold text-gray-400 uppercase tracking-wider">
              {{ day }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="member in teamSchedules" :key="member.id" class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <td class="p-4">
              <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm mr-3">
                  {{ member.avatar }}
                </div>
                <div>
                  <p class="font-bold text-sm text-gray-800">{{ member.name }}</p>
                  <p class="text-[10px] text-gray-400 uppercase font-bold">{{ member.role }}</p>
                </div>
              </div>
            </td>
            <td v-for="(day, index) in member.days" :key="index" class="p-2">
              <div :class="getDayStyle(day.type)" class="rounded-lg border p-2 h-16 flex flex-col justify-center items-center text-center group cursor-pointer hover:shadow-sm transition-all">
                <span class="text-[10px] font-bold block mb-1 uppercase tracking-tighter">{{ day.label }}</span>
                <span v-if="day.time" class="text-[10px] font-medium opacity-80">{{ day.time }}</span>
                <VIcon v-if="day.type === 'off'" icon="mdi-minus" size="14" class="opacity-20" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Legend -->
    <div class="mt-6 flex space-x-6">
      <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
        <div class="w-3 h-3 bg-green-100 border border-green-200 rounded-sm mr-2"></div> Oficina
      </div>
      <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
        <div class="w-3 h-3 bg-blue-50 border border-blue-200 rounded-sm mr-2"></div> Home Office
      </div>
      <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
        <div class="w-3 h-3 bg-orange-50 border border-orange-200 rounded-sm mr-2"></div> Vacaciones
      </div>
      <div class="flex items-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">
        <div class="w-3 h-3 bg-red-50 border border-red-200 rounded-sm mr-2"></div> Ausencia
      </div>
    </div>

  </div>
</template>

<style scoped>
table th, table td {
  min-width: 120px;
}
</style>
