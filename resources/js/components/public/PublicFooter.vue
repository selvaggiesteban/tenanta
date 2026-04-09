<template>
  <VFooter class="public-footer" color="surface-variant">
    <VContainer>
      <VRow>
        <!-- Brand -->
        <VCol cols="12" md="4">
          <div class="d-flex align-center mb-4">
            <VImg
              v-if="branding?.logoLight"
              :src="branding.logoLight"
              height="32"
              width="100"
              contain
            />
            <span v-else class="text-h6 font-weight-bold">
              {{ tenantName }}
            </span>
          </div>
          <p class="text-body-2 text-grey">
            Formación profesional para impulsar tu carrera.
          </p>
        </VCol>

        <!-- Links -->
        <VCol cols="12" md="4">
          <h4 class="text-subtitle-1 font-weight-bold mb-4">Enlaces</h4>
          <VList density="compact" bg-color="transparent">
            <VListItem
              v-for="item in navItems"
              :key="item.path"
              :to="item.path"
              :title="item.label"
              density="compact"
            />
          </VList>
        </VCol>

        <!-- Contact -->
        <VCol cols="12" md="4">
          <h4 class="text-subtitle-1 font-weight-bold mb-4">Contacto</h4>
          <div v-if="branding?.contactInfo">
            <p v-if="branding.contactInfo.email" class="text-body-2">
              <VIcon icon="mdi-email" size="small" class="mr-2" />
              {{ branding.contactInfo.email }}
            </p>
            <p v-if="branding.contactInfo.phone" class="text-body-2 mt-2">
              <VIcon icon="mdi-phone" size="small" class="mr-2" />
              {{ branding.contactInfo.phone }}
            </p>
          </div>
          <div class="mt-4">
            <VBtn
              v-for="social in socialLinks"
              :key="social.icon"
              :href="social.url"
              target="_blank"
              icon
              variant="text"
              size="small"
            >
              <VIcon :icon="social.icon" />
            </VBtn>
          </div>
        </VCol>
      </VRow>

      <VDivider class="my-4" />

      <VRow align="center">
        <VCol cols="12" md="6" class="text-center text-md-left">
          <p class="text-caption text-grey">
            &copy; {{ currentYear }} {{ tenantName }}. Todos los derechos reservados.
          </p>
        </VCol>
        <VCol cols="12" md="6" class="text-center text-md-right">
          <VBtn
            to="/privacy"
            variant="text"
            size="small"
            density="compact"
          >
            Privacidad
          </VBtn>
          <VBtn
            to="/terms"
            variant="text"
            size="small"
            density="compact"
          >
            Términos
          </VBtn>
        </VCol>
      </VRow>
    </VContainer>
  </VFooter>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()

const currentYear = computed(() => new Date().getFullYear())
const branding = computed(() => publicStore.branding)
const tenantName = computed(() => publicStore.tenantName)

const navItems = [
  { path: '/', label: 'Inicio' },
  { path: '/courses', label: 'Cursos' },
  { path: '/pricing', label: 'Precios' },
  { path: '/about', label: 'Nosotros' }
]

const socialLinks = computed(() => {
  if (!branding.value?.socialLinks) return []
  return branding.value.socialLinks
})
</script>
