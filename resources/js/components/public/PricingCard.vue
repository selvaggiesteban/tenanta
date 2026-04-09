<template>
  <VCard
    :class="{ 'featured-plan': featured }"
    :elevation="featured ? 8 : 2"
    class="pricing-card h-100"
  >
    <VCardText class="pa-6">
      <!-- Badge -->
      <VChip
        v-if="featured"
        color="primary"
        class="mb-4"
      >
        Más popular
      </VChip>

      <h3 class="text-h5 font-weight-bold">{{ plan.name }}</h3>
      <p class="text-body-2 text-grey mb-4">{{ plan.description }}</p>

      <!-- Price -->
      <div class="d-flex align-baseline mb-4">
        <span class="text-h3 font-weight-bold">
          ${{ plan.price }}
        </span>
        <span class="text-body-1 text-grey ml-2">
          / {{ durationLabel }}
        </span>
      </div>

      <!-- Features -->
      <VList density="compact" bg-color="transparent">
        <VListItem
          v-for="feature in plan.features"
          :key="feature"
          :title="feature"
          prepend-icon="mdi-check-circle"
        />
      </VList>

      <VBtn
        :to="`/checkout?plan=${plan.id}`"
        :color="featured ? 'primary' : 'secondary'"
        size="large"
        block
        class="mt-4"
      >
        Suscribirse
      </VBtn>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  plan: any
  featured?: boolean
}>()

const durationLabel = computed(() => {
  const days = props.plan.durationDays
  if (days >= 365) return `${days / 365} año${days >= 730 ? 's' : ''}`
  if (days >= 30) return `${Math.floor(days / 30)} mes${days >= 60 ? 'es' : ''}`
  return `${days} días`
})
</script>

<style scoped>
.pricing-card {
  transition: transform 0.2s;
}

.pricing-card:hover {
  transform: translateY(-4px);
}

.featured-plan {
  border: 2px solid rgb(var(--v-theme-primary));
}
</style>
