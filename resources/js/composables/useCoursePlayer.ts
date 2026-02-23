import { ref, computed, onUnmounted } from 'vue'
import { useEnrollmentsStore } from '@/stores/enrollments'
import type { CourseTopic, CourseEnrollment, EnrollmentProgress } from '@/types/courses'

export function useCoursePlayer(enrollment: CourseEnrollment) {
  const enrollmentsStore = useEnrollmentsStore()

  const currentTopic = ref<CourseTopic | null>(null)
  const isPlaying = ref(false)
  const currentTime = ref(0)
  const duration = ref(0)
  const volume = ref(1)
  const isMuted = ref(false)
  const playbackRate = ref(1)
  const isFullscreen = ref(false)
  const showControls = ref(true)

  // Progress tracking
  const lastSavedPosition = ref(0)
  const saveInterval = ref<number | null>(null)
  const SAVE_INTERVAL_MS = 10000 // Save every 10 seconds

  // Computed
  const progress = computed(() => {
    if (!duration.value) return 0
    return (currentTime.value / duration.value) * 100
  })

  const formattedCurrentTime = computed(() => formatTime(currentTime.value))
  const formattedDuration = computed(() => formatTime(duration.value))

  const canMarkComplete = computed(() => {
    if (!currentTopic.value || !duration.value) return false
    // Can mark complete if watched at least 90%
    return currentTime.value >= duration.value * 0.9
  })

  // Methods
  function formatTime(seconds: number): string {
    const mins = Math.floor(seconds / 60)
    const secs = Math.floor(seconds % 60)
    return `${mins}:${secs.toString().padStart(2, '0')}`
  }

  function setTopic(topic: CourseTopic) {
    // Save progress for previous topic
    if (currentTopic.value && currentTime.value > 0) {
      saveProgress()
    }

    currentTopic.value = topic
    currentTime.value = topic.progress?.last_position_seconds || 0
    lastSavedPosition.value = currentTime.value
    isPlaying.value = false
  }

  function play() {
    isPlaying.value = true
    startProgressTracking()
  }

  function pause() {
    isPlaying.value = false
    saveProgress()
  }

  function seek(time: number) {
    currentTime.value = Math.max(0, Math.min(time, duration.value))
  }

  function seekPercent(percent: number) {
    seek((percent / 100) * duration.value)
  }

  function skip(seconds: number) {
    seek(currentTime.value + seconds)
  }

  function setVolume(vol: number) {
    volume.value = Math.max(0, Math.min(1, vol))
    isMuted.value = vol === 0
  }

  function toggleMute() {
    isMuted.value = !isMuted.value
  }

  function setPlaybackRate(rate: number) {
    playbackRate.value = rate
  }

  function toggleFullscreen() {
    isFullscreen.value = !isFullscreen.value
  }

  function onTimeUpdate(time: number) {
    currentTime.value = time
  }

  function onDurationChange(dur: number) {
    duration.value = dur
  }

  function onEnded() {
    isPlaying.value = false
    saveProgress()

    // Auto-mark as complete if not already
    if (canMarkComplete.value && currentTopic.value && !currentTopic.value.progress?.is_completed) {
      markTopicComplete()
    }
  }

  // Progress tracking
  function startProgressTracking() {
    if (saveInterval.value) return

    saveInterval.value = window.setInterval(() => {
      if (isPlaying.value && currentTopic.value) {
        saveProgress()
      }
    }, SAVE_INTERVAL_MS)
  }

  function stopProgressTracking() {
    if (saveInterval.value) {
      clearInterval(saveInterval.value)
      saveInterval.value = null
    }
  }

  async function saveProgress() {
    if (!currentTopic.value || currentTime.value === lastSavedPosition.value) return

    try {
      await enrollmentsStore.updateTopicProgress(
        enrollment.id,
        currentTopic.value.id,
        Math.floor(currentTime.value),
        Math.floor(Math.max(currentTime.value, currentTopic.value.progress?.watch_time_seconds || 0))
      )
      lastSavedPosition.value = currentTime.value
    } catch (error) {
      console.error('Failed to save progress:', error)
    }
  }

  async function markTopicComplete() {
    if (!currentTopic.value) return

    try {
      await enrollmentsStore.markTopicCompleted(enrollment.id, currentTopic.value.id)

      // Update local topic state
      if (currentTopic.value.progress) {
        currentTopic.value.progress.is_completed = true
        currentTopic.value.progress.completed_at = new Date().toISOString()
      }
    } catch (error) {
      console.error('Failed to mark topic complete:', error)
    }
  }

  // Keyboard shortcuts
  function handleKeydown(event: KeyboardEvent) {
    if (event.target instanceof HTMLInputElement || event.target instanceof HTMLTextAreaElement) {
      return
    }

    switch (event.key) {
      case ' ':
      case 'k':
        event.preventDefault()
        isPlaying.value ? pause() : play()
        break
      case 'ArrowLeft':
        event.preventDefault()
        skip(-10)
        break
      case 'ArrowRight':
        event.preventDefault()
        skip(10)
        break
      case 'ArrowUp':
        event.preventDefault()
        setVolume(volume.value + 0.1)
        break
      case 'ArrowDown':
        event.preventDefault()
        setVolume(volume.value - 0.1)
        break
      case 'm':
        event.preventDefault()
        toggleMute()
        break
      case 'f':
        event.preventDefault()
        toggleFullscreen()
        break
      case '0':
      case '1':
      case '2':
      case '3':
      case '4':
      case '5':
      case '6':
      case '7':
      case '8':
      case '9':
        event.preventDefault()
        seekPercent(parseInt(event.key) * 10)
        break
    }
  }

  // Cleanup
  onUnmounted(() => {
    stopProgressTracking()
    if (currentTopic.value && currentTime.value > 0) {
      saveProgress()
    }
  })

  return {
    // State
    currentTopic,
    isPlaying,
    currentTime,
    duration,
    volume,
    isMuted,
    playbackRate,
    isFullscreen,
    showControls,

    // Computed
    progress,
    formattedCurrentTime,
    formattedDuration,
    canMarkComplete,

    // Methods
    setTopic,
    play,
    pause,
    seek,
    seekPercent,
    skip,
    setVolume,
    toggleMute,
    setPlaybackRate,
    toggleFullscreen,
    onTimeUpdate,
    onDurationChange,
    onEnded,
    saveProgress,
    markTopicComplete,
    handleKeydown,
  }
}
