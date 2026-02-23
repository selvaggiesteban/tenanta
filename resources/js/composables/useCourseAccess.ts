import { ref, computed } from 'vue'
import { useEnrollmentsStore } from '@/stores/enrollments'
import { useSubscriptionsStore } from '@/stores/subscriptions'
import type { Course, CourseAccessDetails } from '@/types/courses'

export function useCourseAccess() {
  const enrollmentsStore = useEnrollmentsStore()
  const subscriptionsStore = useSubscriptionsStore()

  const accessDetails = ref<CourseAccessDetails | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Computed
  const canAccess = computed(() => accessDetails.value?.can_access ?? false)
  const accessType = computed(() => accessDetails.value?.access_type ?? 'none')
  const isFree = computed(() => accessDetails.value?.is_free ?? false)
  const hasEnrollment = computed(() => accessDetails.value?.enrollment !== null)
  const hasSubscription = computed(() => accessDetails.value?.subscription !== null)

  const enrollmentProgress = computed(() => {
    return accessDetails.value?.enrollment?.progress_percentage ?? 0
  })

  const accessMessage = computed(() => {
    if (!accessDetails.value) return ''

    switch (accessDetails.value.access_type) {
      case 'free':
        return 'Este curso es gratuito'
      case 'subscription':
        return `Acceso mediante suscripción: ${accessDetails.value.subscription?.plan_name}`
      case 'direct_enrollment':
        return 'Inscrito directamente'
      case 'none':
        if (accessDetails.value.is_free) {
          return 'Inscríbete gratis para comenzar'
        }
        return `Precio: ${formatPrice(accessDetails.value.price, accessDetails.value.currency)}`
      default:
        return ''
    }
  })

  // Methods
  async function checkAccess(courseId: number) {
    loading.value = true
    error.value = null

    try {
      accessDetails.value = await enrollmentsStore.checkCourseAccess(courseId)
      return accessDetails.value
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al verificar acceso'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function enrollInCourse(courseId: number) {
    loading.value = true
    error.value = null

    try {
      // Get active subscription ID if any
      const subscriptionId = subscriptionsStore.currentSubscription?.id

      const enrollment = await enrollmentsStore.enrollInCourse(courseId, subscriptionId)

      // Refresh access details
      await checkAccess(courseId)

      return enrollment
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al inscribirse'
      throw e
    } finally {
      loading.value = false
    }
  }

  function formatPrice(price: number, currency: string): string {
    return new Intl.NumberFormat('es-AR', {
      style: 'currency',
      currency: currency || 'ARS',
    }).format(price)
  }

  function canPreviewTopic(topic: { is_free_preview: boolean }): boolean {
    return topic.is_free_preview || canAccess.value
  }

  function getEnrollButtonText(): string {
    if (canAccess.value) {
      return hasEnrollment.value ? 'Continuar curso' : 'Comenzar curso'
    }

    if (isFree.value) {
      return 'Inscribirse gratis'
    }

    if (subscriptionsStore.hasActiveSubscription) {
      return 'Inscribirse con suscripción'
    }

    return 'Ver planes de suscripción'
  }

  function needsSubscription(): boolean {
    return !canAccess.value && !isFree.value && !subscriptionsStore.hasActiveSubscription
  }

  return {
    // State
    accessDetails,
    loading,
    error,

    // Computed
    canAccess,
    accessType,
    isFree,
    hasEnrollment,
    hasSubscription,
    enrollmentProgress,
    accessMessage,

    // Methods
    checkAccess,
    enrollInCourse,
    formatPrice,
    canPreviewTopic,
    getEnrollButtonText,
    needsSubscription,
  }
}
