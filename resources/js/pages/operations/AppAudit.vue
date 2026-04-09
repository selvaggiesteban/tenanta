<script setup lang="ts">
import { ref } from 'vue'

const categories = ['Productivo', 'Neutral', 'Improductivo']

const apps = ref([
  { id: 1, name: 'Visual Studio Code', category: 'Productivo', users: 12, time: '145h 20m', icon: 'mdi-microsoft-visual-studio-code' },
  { id: 2, name: 'Slack', category: 'Productivo', users: 15, time: '82h 10m', icon: 'mdi-slack' },
  { id: 3, name: 'YouTube', category: 'Improductivo', users: 8, time: '24h 45m', icon: 'mdi-youtube' },
  { id: 4, name: 'LinkedIn', category: 'Neutral', users: 10, time: '12h 30m', icon: 'mdi-linkedin' },
  { id: 5, name: 'Tenanta CRM', category: 'Productivo', users: 15, time: '210h 05m', icon: 'mdi-rocket-launch' },
  { id: 6, name: 'Facebook', category: 'Improductivo', users: 5, time: '18h 15m', icon: 'mdi-facebook' },
  { id: 7, name: 'Spotify', category: 'Neutral', users: 14, time: '95h 40m', icon: 'mdi-spotify' },
])

const filter = ref('Todas')

const getCategoryColor = (cat: string) => {
  switch(cat) {
    case 'Productivo': return 'text-green-600 bg-green-50'
    case 'Improductivo': return 'text-orange-600 bg-orange-50'
    default: return 'text-gray-600 bg-gray-100'
  }
}
</script>

<template>
  <div class="app-audit bg-[#f5f6fa] min-h-screen p-8 font-sans">
    
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Clasificación de Aplicaciones</h1>
        <p class="text-gray-500 text-sm">Define la productividad de las herramientas que usa tu equipo</p>
      </div>
      <button class="bg-[#4caf50] text-white px-6 py-2 rounded-lg text-sm font-bold shadow-md hover:bg-[#43a047] transition-colors">
        Guardar Cambios
      </button>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center text-green-600 mb-2 font-bold text-sm uppercase">
          <VIcon icon="mdi-check-circle-outline" class="mr-2" size="18" /> Productivas
        </div>
        <div class="text-3xl font-black">452</div>
        <p class="text-xs text-gray-400 mt-1">Herramientas que suman al negocio</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center text-orange-600 mb-2 font-bold text-sm uppercase">
          <VIcon icon="mdi-alert-circle-outline" class="mr-2" size="18" /> Improductivas
        </div>
        <div class="text-3xl font-black">128</div>
        <p class="text-xs text-gray-400 mt-1">Fuentes de distracción detectadas</p>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center text-gray-600 mb-2 font-bold text-sm uppercase">
          <VIcon icon="mdi-minus-circle-outline" class="mr-2" size="18" /> Neutrales
        </div>
        <div class="text-3xl font-black">84</div>
        <p class="text-xs text-gray-400 mt-1">Apps de uso administrativo o mixto</p>
      </div>
    </div>

    <!-- Filters & Table Area -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
      <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
        <div class="flex space-x-2">
          <button v-for="f in ['Todas', 'Productivo', 'Neutral', 'Improductivo']" :key="f"
                  @click="filter = f"
                  :class="filter === f ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-gray-600 hover:bg-gray-100'"
                  class="px-4 py-1.5 rounded-full text-xs font-bold transition-all border border-gray-200">
            {{ f }}
          </button>
        </div>
        <div class="relative">
          <VIcon icon="mdi-magnify" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size="18" />
          <input type="text" placeholder="Buscar app o sitio..." class="pl-10 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none w-64">
        </div>
      </div>

      <table class="w-full">
        <thead>
          <tr class="text-left bg-gray-50">
            <th class="p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Aplicación / Sitio Web</th>
            <th class="p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Clasificación</th>
            <th class="p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Usuarios</th>
            <th class="p-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Tiempo Total</th>
            <th class="p-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="app in apps" :key="app.id" class="border-b border-gray-50 hover:bg-indigo-50/30 transition-colors">
            <td class="p-4">
              <div class="flex items-center">
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center mr-3 text-gray-500">
                  <VIcon :icon="app.icon" size="24" />
                </div>
                <span class="font-bold text-sm text-gray-800">{{ app.name }}</span>
              </div>
            </td>
            <td class="p-4">
              <select v-model="app.category" 
                      :class="getCategoryColor(app.category)"
                      class="px-3 py-1.5 rounded-lg text-xs font-bold border-none cursor-pointer outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
            </td>
            <td class="p-4 text-sm font-medium text-gray-600">{{ app.users }} miembros</td>
            <td class="p-4 text-sm font-bold text-gray-800">{{ app.time }}</td>
            <td class="p-4 text-right">
              <button class="text-gray-400 hover:text-indigo-600 p-1">
                <VIcon icon="mdi-chart-box-outline" size="20" />
              </button>
              <button class="text-gray-400 hover:text-red-600 p-1 ml-2">
                <VIcon icon="mdi-delete-outline" size="20" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>
