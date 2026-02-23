import { ref, computed, onUnmounted, watch } from 'vue'

export function useTestTimer(initialSeconds: number | null, onTimeout?: () => void) {
  const remainingSeconds = ref(initialSeconds ?? 0)
  const isRunning = ref(false)
  const intervalId = ref<number | null>(null)
  const hasTimedOut = ref(false)

  // Computed
  const hasTimeLimit = computed(() => initialSeconds !== null && initialSeconds > 0)

  const formattedTime = computed(() => {
    if (!hasTimeLimit.value) return '--:--'

    const hours = Math.floor(remainingSeconds.value / 3600)
    const minutes = Math.floor((remainingSeconds.value % 3600) / 60)
    const seconds = remainingSeconds.value % 60

    if (hours > 0) {
      return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
    }
    return `${minutes}:${seconds.toString().padStart(2, '0')}`
  })

  const isLowTime = computed(() => {
    if (!hasTimeLimit.value) return false
    return remainingSeconds.value <= 60 // Last minute warning
  })

  const isCriticalTime = computed(() => {
    if (!hasTimeLimit.value) return false
    return remainingSeconds.value <= 30 // Last 30 seconds
  })

  const progressPercent = computed(() => {
    if (!hasTimeLimit.value || !initialSeconds) return 100
    return (remainingSeconds.value / initialSeconds) * 100
  })

  // Methods
  function start() {
    if (!hasTimeLimit.value || isRunning.value || hasTimedOut.value) return

    isRunning.value = true
    intervalId.value = window.setInterval(() => {
      if (remainingSeconds.value > 0) {
        remainingSeconds.value--
      } else {
        stop()
        hasTimedOut.value = true
        onTimeout?.()
      }
    }, 1000)
  }

  function stop() {
    if (intervalId.value) {
      clearInterval(intervalId.value)
      intervalId.value = null
    }
    isRunning.value = false
  }

  function pause() {
    stop()
  }

  function resume() {
    if (!hasTimedOut.value) {
      start()
    }
  }

  function reset(seconds?: number) {
    stop()
    remainingSeconds.value = seconds ?? initialSeconds ?? 0
    hasTimedOut.value = false
  }

  function updateTime(seconds: number) {
    remainingSeconds.value = Math.max(0, seconds)
    if (remainingSeconds.value === 0 && !hasTimedOut.value) {
      hasTimedOut.value = true
      stop()
      onTimeout?.()
    }
  }

  // Cleanup
  onUnmounted(() => {
    stop()
  })

  return {
    // State
    remainingSeconds,
    isRunning,
    hasTimedOut,

    // Computed
    hasTimeLimit,
    formattedTime,
    isLowTime,
    isCriticalTime,
    progressPercent,

    // Methods
    start,
    stop,
    pause,
    resume,
    reset,
    updateTime,
  }
}
