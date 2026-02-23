<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useBrandingStore } from '@/stores/branding'
import { useTheme } from 'vuetify'
import { useI18n } from 'vue-i18n'

const authStore = useAuthStore()
const brandingStore = useBrandingStore()
const theme = useTheme()
const { t, locale } = useI18n()
const router = useRouter()
const route = useRoute()

const mobileMenu = ref(false)

const toggleTheme = () => {
  theme.global.name.value = theme.global.current.value.dark ? 'light' : 'dark'
}

const toggleLocale = () => {
  locale.value = locale.value === 'es-AR' ? 'en-US' : 'es-AR'
}

const navItems = computed(() => [
  { title: t('public.home'), to: '/', exact: true },
  { title: t('public.courses'), to: '/courses' },
  { title: t('public.pricing'), to: '/plans' },
  { title: t('public.about'), to: '/about' },
  { title: t('public.contact'), to: '/contact' },
])

const isActive = (path: string, exact = false) => {
  if (exact) {
    return route.path === path
  }
  return route.path.startsWith(path)
}

const goToLogin = () => {
  router.push({ name: 'login' })
}

const goToDashboard = () => {
  router.push({ name: 'dashboard' })
}

const currentYear = new Date().getFullYear()
</script>

<template>
  <VAppBar
    elevation="0"
    :color="theme.global.current.value.dark ? 'surface' : 'white'"
    border
  >
    <VContainer class="d-flex align-center">
      <!-- Logo -->
      <RouterLink to="/" class="d-flex align-center text-decoration-none">
        <VAvatar
          v-if="brandingStore.branding?.logo_url"
          :image="brandingStore.branding.logo_url"
          size="40"
          rounded="0"
        />
        <span class="text-h5 font-weight-bold text-primary ml-2">
          {{ brandingStore.branding?.company_name || 'Tenanta' }}
        </span>
      </RouterLink>

      <VSpacer />

      <!-- Desktop Navigation -->
      <div class="d-none d-md-flex align-center ga-1">
        <VBtn
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          variant="text"
          :color="isActive(item.to, item.exact) ? 'primary' : undefined"
          rounded="pill"
        >
          {{ item.title }}
        </VBtn>
      </div>

      <VSpacer />

      <!-- Actions -->
      <div class="d-flex align-center ga-1">
        <VBtn icon variant="text" size="small" @click="toggleLocale">
          <VIcon>mdi-translate</VIcon>
          <VTooltip activator="parent">
            {{ locale === 'es-AR' ? 'English' : 'Español' }}
          </VTooltip>
        </VBtn>

        <VBtn icon variant="text" size="small" @click="toggleTheme">
          <VIcon>{{ theme.global.current.value.dark ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</VIcon>
        </VBtn>

        <VBtn
          v-if="authStore.isAuthenticated"
          color="primary"
          variant="tonal"
          class="d-none d-sm-flex"
          @click="goToDashboard"
        >
          {{ t('nav.dashboard') }}
        </VBtn>

        <VBtn
          v-else
          color="primary"
          variant="flat"
          class="d-none d-sm-flex"
          @click="goToLogin"
        >
          {{ t('public.login') }}
        </VBtn>

        <!-- Mobile Menu Button -->
        <VBtn
          icon
          variant="text"
          class="d-md-none"
          @click="mobileMenu = true"
        >
          <VIcon>mdi-menu</VIcon>
        </VBtn>
      </div>
    </VContainer>
  </VAppBar>

  <!-- Mobile Navigation Drawer -->
  <VNavigationDrawer
    v-model="mobileMenu"
    location="right"
    temporary
  >
    <VList>
      <VListItem
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        :title="item.title"
        @click="mobileMenu = false"
      />
      <VDivider class="my-2" />
      <VListItem
        v-if="authStore.isAuthenticated"
        :title="t('nav.dashboard')"
        prepend-icon="mdi-view-dashboard"
        @click="goToDashboard"
      />
      <VListItem
        v-else
        :title="t('public.login')"
        prepend-icon="mdi-login"
        @click="goToLogin"
      />
    </VList>
  </VNavigationDrawer>

  <VMain>
    <RouterView />
  </VMain>

  <!-- Footer -->
  <VFooter class="bg-surface pa-0">
    <VContainer>
      <VRow class="py-8">
        <!-- Company Info -->
        <VCol cols="12" md="4" class="mb-4 mb-md-0">
          <h3 class="text-h6 font-weight-bold mb-3">
            {{ brandingStore.branding?.company_name || 'Tenanta' }}
          </h3>
          <p class="text-body-2 text-medium-emphasis mb-4">
            {{ t('public.footer_description') }}
          </p>
          <div class="d-flex ga-2">
            <VBtn
              v-if="brandingStore.branding?.social_links?.facebook"
              icon
              variant="tonal"
              size="small"
              :href="brandingStore.branding.social_links.facebook"
              target="_blank"
            >
              <VIcon>mdi-facebook</VIcon>
            </VBtn>
            <VBtn
              v-if="brandingStore.branding?.social_links?.twitter"
              icon
              variant="tonal"
              size="small"
              :href="brandingStore.branding.social_links.twitter"
              target="_blank"
            >
              <VIcon>mdi-twitter</VIcon>
            </VBtn>
            <VBtn
              v-if="brandingStore.branding?.social_links?.instagram"
              icon
              variant="tonal"
              size="small"
              :href="brandingStore.branding.social_links.instagram"
              target="_blank"
            >
              <VIcon>mdi-instagram</VIcon>
            </VBtn>
            <VBtn
              v-if="brandingStore.branding?.social_links?.linkedin"
              icon
              variant="tonal"
              size="small"
              :href="brandingStore.branding.social_links.linkedin"
              target="_blank"
            >
              <VIcon>mdi-linkedin</VIcon>
            </VBtn>
          </div>
        </VCol>

        <!-- Quick Links -->
        <VCol cols="6" md="2">
          <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ t('public.quick_links') }}</h4>
          <VList density="compact" class="bg-transparent pa-0">
            <VListItem
              v-for="item in navItems"
              :key="item.to"
              :to="item.to"
              :title="item.title"
              class="px-0 min-height-auto"
            />
          </VList>
        </VCol>

        <!-- Resources -->
        <VCol cols="6" md="2">
          <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ t('public.resources') }}</h4>
          <VList density="compact" class="bg-transparent pa-0">
            <VListItem to="/courses" :title="t('public.courses')" class="px-0 min-height-auto" />
            <VListItem to="/plans" :title="t('public.pricing')" class="px-0 min-height-auto" />
            <VListItem href="#" :title="t('public.blog')" class="px-0 min-height-auto" />
            <VListItem href="#" :title="t('public.help')" class="px-0 min-height-auto" />
          </VList>
        </VCol>

        <!-- Legal -->
        <VCol cols="12" md="4">
          <h4 class="text-subtitle-1 font-weight-bold mb-3">{{ t('public.contact_us') }}</h4>
          <div class="text-body-2 text-medium-emphasis">
            <p v-if="brandingStore.branding?.contact_email" class="mb-2">
              <VIcon size="small" class="mr-2">mdi-email-outline</VIcon>
              {{ brandingStore.branding.contact_email }}
            </p>
            <p v-if="brandingStore.branding?.contact_phone" class="mb-2">
              <VIcon size="small" class="mr-2">mdi-phone-outline</VIcon>
              {{ brandingStore.branding.contact_phone }}
            </p>
            <p v-if="brandingStore.branding?.address">
              <VIcon size="small" class="mr-2">mdi-map-marker-outline</VIcon>
              {{ brandingStore.branding.address }}
            </p>
          </div>
        </VCol>
      </VRow>

      <VDivider />

      <VRow class="py-4">
        <VCol cols="12" md="6" class="text-center text-md-start">
          <span class="text-body-2 text-medium-emphasis">
            © {{ currentYear }} {{ brandingStore.branding?.company_name || 'Tenanta' }}. {{ t('public.all_rights_reserved') }}
          </span>
        </VCol>
        <VCol cols="12" md="6" class="text-center text-md-end">
          <RouterLink to="/privacy" class="text-body-2 text-medium-emphasis text-decoration-none mr-4">
            {{ t('public.privacy_policy') }}
          </RouterLink>
          <RouterLink to="/terms" class="text-body-2 text-medium-emphasis text-decoration-none">
            {{ t('public.terms_of_service') }}
          </RouterLink>
        </VCol>
      </VRow>
    </VContainer>
  </VFooter>
</template>

<style scoped>
.min-height-auto {
  min-height: 32px !important;
}
</style>
