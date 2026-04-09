<script setup lang="ts">
import { ref } from 'vue'
import api from '@/api'

const step = ref(1)
const file = ref<File | null>(null)
const headers = ref([])
const previewRows = ref([])
const tempPath = ref('')
const mapping = ref({
  business_name: '',
  email: '',
  phone: '',
  address: '',
  category: '',
  descriptions: ''
})

const handleFileUpload = async (event: any) => {
  const formData = new FormData()
  formData.append('file', event.target.files[0])
  
  try {
    const response = await api.post('/admin/import/preview', formData)
    headers.value = response.data.headers
    previewRows.value = response.data.preview_rows
    tempPath.value = response.data.temp_path
    step.value = 2
  } catch (e) { alert('Error al procesar el archivo') }
}

const startImport = async () => {
  try {
    const response = await api.post('/admin/import/process', {
      temp_path: tempPath.value,
      mapping: mapping.value
    })
    alert(response.data.message)
    step.value = 3
  } catch (e) { alert('Error en la importación') }
}
</script>

<template>
  <div class="import-module p-8 bg-white min-h-screen">
    <h1 class="text-3xl font-black mb-8">Importador de Inquilinos</h1>

    <!-- Paso 1: Subida -->
    <div v-if="step === 1" class="border-4 border-dashed p-20 text-center rounded-3xl">
      <VIcon icon="mdi-file-excel" size="64" color="green" />
      <h2 class="text-xl font-bold mt-4">Sube tu archivo CSV / XLSX</h2>
      <p class="text-gray-500 mb-8">Los campos se mapearán automáticamente en el siguiente paso.</p>
      <input type="file" @change="handleFileUpload" class="hidden" id="fileInput">
      <VBtn color="success" size="large" @click="() => document.getElementById('fileInput').click()">
        Seleccionar Archivo
      </VBtn>
    </div>

    <!-- Paso 2: Mapeo -->
    <div v-if="step === 2" class="grid grid-cols-1 lg:grid-cols-2 gap-12">
      <div>
        <h2 class="text-xl font-bold mb-6">Mapeo de Campos Clave</h2>
        <div v-for="(val, key) in mapping" :key="key" class="mb-4">
          <label class="block text-xs font-black uppercase text-gray-400 mb-1">{{ key }}</label>
          <select v-model="mapping[key]" class="w-full p-3 border rounded-xl bg-gray-50">
            <option value="">-- Seleccionar Columna --</option>
            <option v-for="h in headers" :key="h" :value="h">{{ h }}</option>
          </select>
        </div>
        <VBtn color="indigo" block size="large" @click="startImport" class="mt-8">
          Iniciar Importación Masiva
        </VBtn>
      </div>

      <div class="bg-gray-50 p-6 rounded-2xl border">
        <h3 class="font-bold mb-4">Previsualización de Datos</h3>
        <pre class="text-[10px] text-gray-600">{{ previewRows }}</pre>
      </div>
    </div>

    <!-- Paso 3: Éxito -->
    <div v-if="step === 3" class="text-center p-20">
      <VIcon icon="mdi-check-decagram" size="100" color="success" />
      <h2 class="text-4xl font-black mt-6">¡Importación Exitosa!</h2>
      <p class="text-gray-500 mt-4">Los inquilinos han sido creados y los emails de activación enviados.</p>
      <VBtn @click="step = 1" variant="text" class="mt-8">Importar otro archivo</VBtn>
    </div>
  </div>
</template>
