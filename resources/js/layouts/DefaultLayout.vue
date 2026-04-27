<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useTheme } from 'vuetify'

const authStore = useAuthStore()
const router = useRouter()
const theme = useTheme()
const drawer = ref(true)

const hasRole = (_role: string) => {
  // Placeholder for role-based visibility logic
  // For now, we return true so the links are visible during development
  return true
}

const toggleTheme = () => {
  theme.global.name.value = theme.global.current.value.dark ? 'light' : 'dark'
}

const handleLogout = async () => {
  await authStore.logout()
  router.push({ name: 'login' })
}

const navItems = [
  { title: 'Dashboard', icon: 'mdi-view-dashboard', to: '/dashboard' },
  { divider: true },
  { title: 'CRM', header: true },
  { title: 'Clientes', icon: 'mdi-account-group', to: '/crm/clients' },
  { title: 'Leads', icon: 'mdi-account-search', to: '/crm/leads' },
  { title: 'Pipeline', icon: 'mdi-view-column', to: '/crm/kanban' },
  { title: 'Presupuestos', icon: 'mdi-file-document-outline', to: '/crm/quotes' },
  {
    title: 'Marketing Digital',
    header: true
  },
  {
    title: 'Automatizaciones',
    icon: 'mdi-robot',
    children: [
      { title: 'Blog (Editorial)', to: '/marketing/automations/blog', icon: 'mdi-post' },
      { title: 'Encuestas', to: '/marketing/automations/surveys', icon: 'mdi-form-select' },
      { title: 'Form Tester', to: '/marketing/automations/form-tester', icon: 'mdi-test-tube' },
      { title: 'Generador Legal', to: '/marketing/automations/legal', icon: 'mdi-gavel' },
      { title: 'GBP Scraper', to: '/marketing/automations/scraper', icon: 'mdi-map-marker-radius' },
      { title: 'Landing Builder', to: '/marketing/automations/landing-builder', icon: 'mdi-web' },
      { title: 'WhatsApp Growth', to: '/marketing/automations/whatsapp', icon: 'mdi-whatsapp' },
      { title: 'SEO Crawler', to: '/marketing/automations/seo', icon: 'mdi-magnify-scan' },
    ]
  },
  {
    title: 'Campañas',
    icon: 'mdi-bullhorn-outline',
    children: [
      { title: 'Email Marketing', to: '/marketing/campaigns/email', icon: 'mdi-email-fast-outline' },
      {
        title: 'Conversiones',
        icon: 'mdi-tray-arrow-down',
        children: [
          { title: 'Email Inbox', to: '/marketing/conversiones/email', icon: 'mdi-email-outline' },
          { title: 'Messenger', to: '/marketing/conversiones/messenger', icon: 'mdi-facebook-messenger' },
          { title: 'WhatsApp', to: '/marketing/conversiones/whatsapp', icon: 'mdi-whatsapp' },
        ]
      }
    ]
  },
  { divider: true },
  { title: 'Operaciones', header: true },
  { title: 'Proyectos', icon: 'mdi-folder-multiple', to: '/projects' },
  { title: 'Tareas', icon: 'mdi-checkbox-marked-outline', to: '/tasks' },
  { title: 'Time Tracking', icon: 'mdi-timer-outline', to: '/time' },
  { divider: true },
  { title: 'Soporte', header: true },
  { title: 'Tickets', icon: 'mdi-ticket-outline', to: '/support/tickets' },
  { title: 'Base de Conocimientos', icon: 'mdi-book-open-variant', to: '/support/kb' },
  { divider: true },
  { title: 'Chat AI', icon: 'mdi-robot', to: '/chat' },
  { title: 'Configuración', icon: 'mdi-cog-outline', to: '/settings' },
]
</script>

<template>
  <VNavigationDrawer
    v-model="drawer"
    :width="260"
    elevation="0"
  >
    <div class="pa-4">
      <h2 class="text-h5 font-weight-bold text-primary">
        Tenanta
      </h2>
      <p class="text-caption text-medium-emphasis">
        {{ authStore.user?.tenant?.name }}
      </p>
    </div>

    <VDivider />

    <VList nav density="compact">
      <template v-for="(item, i) in navItems" :key="i">
        <VDivider v-if="item.divider" class="my-2" />
        <VListSubheader v-else-if="item.header">
          {{ item.title }}
        </VListSubheader>
        <VListItem
          v-else-if="!item.roles || item.roles.some(r => hasRole(r))"
          :to="item.to"
          :prepend-icon="item.icon"
          :title="item.title"
          rounded="lg"
        />
      </template>
    </VList>
  </VNavigationDrawer>

  <VAppBar elevation="0" border>
    <VAppBarNavIcon @click="drawer = !drawer" />

    <VSpacer />

    <VBtn icon variant="text" @click="toggleTheme">
      <VIcon>{{ theme.global.current.value.dark ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</VIcon>
    </VBtn>

    <VBtn icon variant="text">
      <VIcon>mdi-bell-outline</VIcon>
    </VBtn>

    <VMenu>
      <template #activator="{ props }">
        <VBtn v-bind="props" variant="text" class="ms-2">
          <VAvatar color="primary" size="36">
            {{ authStore.userName?.charAt(0).toUpperCase() }}
          </VAvatar>
        </VBtn>
      </template>
      <VList>
        <VListItem prepend-icon="mdi-account" title="Mi Perfil" />
        <VListItem prepend-icon="mdi-cog" title="Configuración" to="/settings" />
        <VDivider />
        <VListItem
          prepend-icon="mdi-logout"
          title="Cerrar Sesión"
          @click="handleLogout"
        />
      </VList>
    </VMenu>
  </VAppBar>

  <VMain>
    <VContainer fluid class="pa-6">
      <RouterView />
    </VContainer>
  </VMain>

  <!-- Timer Widget Slot -->
  <div id="timer-widget-portal" />
</template>
