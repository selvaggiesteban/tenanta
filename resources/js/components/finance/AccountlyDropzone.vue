<script setup lang="ts">
import { ref } from 'vue'
import api from '@/api'

const isDragging = ref(false)
const uploadStatus = ref('')
const files = ref([])

const handleFileDrop = (e: DragEvent) => {
  isDragging.value = false
  const droppedFiles = e.dataTransfer?.files
  if (droppedFiles) {
    processFiles(droppedFiles)
  }
}

const processFiles = async (fileList: FileList) => {
  uploadStatus.value = 'Procesando transcripción inteligente...'
  
  // Simulación de envío al Job de Laravel que dispara Accountly Python
  setTimeout(() => {
    uploadStatus.value = '¡Éxito! Resumen transcrito. Los gráficos se han actualizado.'
    files.value.push(fileList[0].name)
  }, 3000)
}
</script>

<template>
  <div class="accountly-upload p-8 bg-gray-50 rounded-3xl border-2 border-dashed transition-all"
       :class="isDragging ? 'border-indigo-600 bg-indigo-50 scale-[1.02]' : 'border-gray-300'"
       @dragover.prevent="isDragging = true"
       @dragleave.prevent="isDragging = false"
       @drop.prevent="handleFileDrop">
    
    <div class="flex flex-col items-center text-center">
      <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center mb-6 text-indigo-600">
        <VIcon :icon="uploadStatus.includes('Éxito') ? 'mdi-check-circle' : 'mdi-cloud-upload-outline'" size="48" />
      </div>
      
      <h2 class="text-2xl font-black text-gray-900 mb-2">Automatización Inteligente Accountly</h2>
      <p class="text-gray-600 max-w-sm mb-8 leading-relaxed">
        Arrastra tu resumen de cuenta (PDF/Excel) aquí para transcribir automáticamente tus ingresos y gastos a los dashboards de Tenanta.
      </p>

      <div v-if="uploadStatus" class="mb-6 p-4 rounded-xl font-bold"
           :class="uploadStatus.includes('Éxito') ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700 animate-pulse'">
        {{ uploadStatus }}
      </div>

      <button class="bg-gray-900 text-white px-8 py-3 rounded-xl font-bold hover:bg-black transition-colors shadow-lg">
        Seleccionar Archivo
      </button>

      <div v-if="files.length > 0" class="mt-8 w-full max-w-md">
        <h4 class="text-xs font-bold uppercase text-gray-400 mb-2 text-left">Archivos Recientes</h4>
        <div v-for="file in files" :key="file" class="bg-white p-3 rounded-lg flex items-center border mb-2">
          <VIcon icon="mdi-file-document-outline" color="indigo" class="mr-2" />
          <span class="text-sm font-medium text-gray-700">{{ file }}</span>
          <VIcon icon="mdi-check" color="green" class="ml-auto" size="16" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.accountly-upload {
  cursor: pointer;
}
</style>
