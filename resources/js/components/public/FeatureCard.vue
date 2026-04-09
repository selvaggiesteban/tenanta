<template>
  <VCard
    class="feature-card h-100"
    :variant="variant"
  >
    <VCardText class="text-center pa-6">
      <VAvatar
        :color="iconColor"
        size="64"
        class="mb-4"
      >
        <VIcon :icon="icon" size="32" />
      </VAvatar>
      <h3 class="text-h6 font-weight-bold mb-2">{{ title }}</h3>
      <p class="text-body-2 text-grey">{{ description }}</p>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
defineProps<{
  icon: string
  iconColor?: string
  title: string
  description: string
  variant?: 'elevated' | 'flat' | 'tonal'
}>()
</script>

<style scoped>
.feature-card {
  transition: transform 0.2s;
}

.feature-card:hover {
  transform: translateY(-4px);
}
</style>
