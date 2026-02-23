<script setup lang="ts">
import DefaultLayout from '@/layouts/DefaultLayout.vue'
import BlankLayout from '@/layouts/BlankLayout.vue'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const authStore = useAuthStore()

// Initialize auth on app load
onMounted(async () => {
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchUser()
    } catch {
      await authStore.logout()
    }
  }
})

const layout = computed(() => {
  return route.meta.layout === 'blank' ? BlankLayout : DefaultLayout
})
</script>

<template>
  <VApp>
    <component :is="layout">
      <RouterView />
    </component>
  </VApp>
</template>
