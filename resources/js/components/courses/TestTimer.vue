<template>
  <VChip
    :color="timeColor"
    size="large"
  >
    <VIcon start icon="mdi-clock" />
    {{ formattedTime }}
  </VChip>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps<{
  duration: number // in seconds
  startTime: string
}>()

const emit = defineEmits<{
  (e: 'timeout'): void
}>()

const remaining = ref(0)
let interval: ReturnType<typeof setInterval> | null = null

const formattedTime = computed(() => {
  const minutes = Math.floor(remaining.value / 60)
  const seconds = remaining.value % 60
  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
})

const timeColor = computed(() => {
  if (remaining.value < 60) return 'error'
  if (remaining.value < 300) return 'warning'
  return 'primary'
})

onMounted(() => {
  const start = new Date(props.startTime).getTime()
  const end = start + props.duration * 1000

  interval = setInterval(() => {
    const now = Date.now()
    remaining.value = Math.max(0, Math.floor((end - now) / 1000))

    if (remaining.value === 0) {
      if (interval) clearInterval(interval)
      emit('timeout')
    }
  }, 1000)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>
