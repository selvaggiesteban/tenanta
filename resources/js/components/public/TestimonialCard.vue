<template>
  <VCard class="testimonial-card h-100">
    <VCardText class="pa-6">
      <div class="d-flex mb-4">
        <VIcon
          v-for="i in 5"
          :key="i"
          icon="mdi-star"
          :color="i <= rating ? 'warning' : 'grey-lighten-2'"
        />
      </div>

      <p class="text-body-1 mb-4">{{ text }}</p>

      <div class="d-flex align-center">
        <VAvatar
          v-if="avatar"
          :image="avatar"
          size="48"
          class="mr-3"
        />
        <VAvatar
          v-else
          color="primary"
          size="48"
          class="mr-3"
        >
          {{ initials }}
        </VAvatar>
        <div>
          <p class="font-weight-bold mb-0">{{ name }}</p>
          <p class="text-caption text-grey">{{ role }}</p>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  text: string
  name: string
  role: string
  rating: number
  avatar?: string
}>()

const initials = computed(() => {
  return props.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})
</script>
