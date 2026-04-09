<template>
  <div v-if="loading" class="d-flex align-center justify-center" style="height: 100vh">
    <VProgressCircular indeterminate color="primary" size="64" />
  </div>
  <div v-else-if="error" class="d-flex flex-column align-center justify-center" style="height: 100vh">
    <h1 class="text-h2 mb-4">404</h1>
    <p class="text-h5 mb-6">{{ error }}</p>
    <VBtn to="/" color="primary">Volver al inicio</VBtn>
  </div>
  <HomePage v-else />
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { usePublicStore } from '@/stores/public'
import HomePage from './HomePage.vue'

const route = useRoute()
const publicStore = usePublicStore()
const loading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    const slug = route.params.slug as string
    if (slug) {
      await publicStore.fetchBrandingBySlug(slug)
    } else {
      // Try to detect from subdomain or host
      const host = window.location.hostname
      const parts = host.split('.')
      
      // If we are on tenant.selvaggiconsultores.com or tenant.localhost
      if (parts.length >= 3 && parts[0] !== 'www' && parts[0] !== 'google') {
        await publicStore.fetchBrandingBySlug(parts[0])
      } else {
        // Fallback or default landing page
        await publicStore.fetchBranding()
      }
    }
  } catch (err: any) {
    error.value = 'No se pudo encontrar la página solicitada.'
    console.error('Landing page error:', err)
  } finally {
    loading.value = false
  }
})
</script>
