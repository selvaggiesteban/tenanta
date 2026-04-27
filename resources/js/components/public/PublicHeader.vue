<template>
  <VAppBar
    :color="scrolled ? 'background' : 'transparent'"
    :elevation="scrolled ? 2 : 0"
    scroll-behavior="hide"
    class="public-header"
  >
    <VContainer class="d-flex align-center">
      <!-- Logo -->
      <RouterLink to="/" class="d-flex align-center text-decoration-none">
        <VImg
          v-if="branding?.logoLight"
          :src="branding.logoLight"
          height="40"
          width="120"
          contain
        />
        <span v-else class="text-h5 font-weight-bold">
          {{ tenantName }}
        </span>
      </RouterLink>

      <VSpacer />

      <!-- Desktop Nav -->
      <VBtn
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        variant="text"
        class="hidden-sm-and-down"
      >
        {{ item.label }}
      </VBtn>

      <VBtn
        to="/app"
        color="primary"
        variant="elevated"
        class="ml-4 hidden-sm-and-down"
      >
        Acceder
      </VBtn>

      <!-- Mobile Menu -->
      <VAppBarNavIcon
        class="hidden-md-and-up"
        @click="drawer = !drawer"
      />
    </VContainer>
  </VAppBar>

  <!-- Mobile Drawer -->
  <VNavigationDrawer
    v-model="drawer"
    location="right"
    temporary
  >
    <VList>
      <VListItem
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        :title="item.label"
      />
      <VDivider class="my-2" />
      <VListItem to="/app" title="Acceder" />
    </VList>
  </VNavigationDrawer>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useWindowScroll } from '@vueuse/core'
import { usePublicStore } from '@/stores/public'
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const publicStore = usePublicStore()
const drawer = ref(false)
const { y } = useWindowScroll()

const setLocale = (lang: string) => {
  locale.value = lang
  localStorage.setItem('locale', lang)
}

const scrolled = computed(() => y.value > 50)

const navItems = [
  { path: '/', label: 'Inicio' },
  { path: '/courses', label: 'Cursos' },
  { path: '/pricing', label: 'Precios' },
  { path: '/about', label: 'Nosotros' },
  { path: '/contact', label: 'Contacto' }
]

const branding = computed(() => publicStore.branding)
const tenantName = computed(() => publicStore.tenantName)

onMounted(() => {
  publicStore.fetchBranding()
})
</script>
cStore = usePublicStore()
const drawer = ref(false)
const { y } = useWindowScroll()

const scrolled = computed(() => y.value > 50)

const navItems = [
  { path: '/', label: 'Inicio' },
  { path: '/courses', label: 'Cursos' },
  { path: '/pricing', label: 'Precios' },
  { path: '/about', label: 'Nosotros' },
  { path: '/contact', label: 'Contacto' }
]

const branding = computed(() => publicStore.branding)
const tenantName = computed(() => publicStore.tenantName)

onMounted(() => {
  publicStore.fetchBranding()
})
</script>
