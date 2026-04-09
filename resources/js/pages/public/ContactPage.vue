<template>
  <div class="py-12">
    <VContainer>
      <VRow justify="center">
        <VCol cols="12" md="8">
          <h1 class="text-h3 font-weight-bold text-center mb-4">Contacto</h1>
          <p class="text-h6 text-grey text-center mb-8">
            ¿Tienes preguntas? Estamos aquí para ayudarte.
          </p>
        </VCol>
      </VRow>

      <VRow>
        <VCol cols="12" md="6">
          <VCard>
            <VCardText class="pa-6">
              <h2 class="text-h5 font-weight-bold mb-4">Envíanos un mensaje</h2>
              <ContactForm />
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="6">
          <VCard class="h-100" color="primary" theme="dark">
            <VCardText class="pa-6">
              <h2 class="text-h5 font-weight-bold mb-6">Información de contacto</h2>

              <div class="mb-6">
                <VIcon icon="mdi-email" size="32" class="mb-2" />
                <p class="text-h6">Email</p>
                <p>{{ branding?.contactInfo?.email || 'contacto@ejemplo.com' }}</p>
              </div>

              <div class="mb-6">
                <VIcon icon="mdi-phone" size="32" class="mb-2" />
                <p class="text-h6">Teléfono</p>
                <p>{{ branding?.contactInfo?.phone || '+54 11 1234-5678' }}</p>
              </div>

              <div>
                <VIcon icon="mdi-map-marker" size="32" class="mb-2" />
                <p class="text-h6">Dirección</p>
                <p>{{ branding?.contactInfo?.address || 'Buenos Aires, Argentina' }}</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ContactForm from '@/components/public/ContactForm.vue'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()
const branding = computed(() => publicStore.branding)
</script>
