import { defineStore } from 'pinia'
import api from '@/api'
import type {
  CourseEnrollment,
  EnrollmentProgress,
  TopicProgress,
  CourseAccessDetails,
} from '@/types/courses'

interface EnrollmentsState {
  enrollments: CourseEnrollment[]
  currentEnrollment: CourseEnrollment | null
  currentProgress: EnrollmentProgress | null
  loading: boolean
  error: string | null
}

export const useEnrollmentsStore = defineStore('enrollments', {
  state: (): EnrollmentsState => ({
    enrollments: [],
    currentEnrollment: null,
    currentProgress: null,
    loading: false,
    error: null,
  }),

  getters: {
    activeEnrollments: (state) => state.enrollments.filter(e => e.status === 'active'),
    completedEnrollments: (state) => state.enrollments.filter(e => e.status === 'completed'),
    inProgressCourses: (state) => state.enrollments.filter(e => e.status === 'active' && e.progress_percentage > 0),
  },

  actions: {
    // Fetch user enrollments
    async fetchEnrollments(status?: string) {
      this.loading = true
      this.error = null

      try {
        const params: any = {}
        if (status) params.status = status

        const response = await api.get('/enrollments', { params })
        this.enrollments = response.data.data || []
        return this.enrollments
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar inscripciones'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get single enrollment with progress
    async fetchEnrollment(enrollmentId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/enrollments/${enrollmentId}`)
        this.currentEnrollment = response.data.enrollment
        this.currentProgress = response.data.progress
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar inscripción'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get course content for enrolled user
    async fetchCourseContent(enrollmentId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/enrollments/${enrollmentId}/content`)
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar contenido'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Enroll in a course
    async enrollInCourse(courseId: number, subscriptionId?: number) {
      this.loading = true
      this.error = null

      try {
        const data: any = { course_id: courseId }
        if (subscriptionId) data.subscription_id = subscriptionId

        const response = await api.post('/enrollments', data)
        const enrollment = response.data.data
        this.enrollments.unshift(enrollment)
        return enrollment
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al inscribirse'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Unenroll from a course
    async unenroll(enrollmentId: number) {
      this.loading = true
      this.error = null

      try {
        await api.delete(`/enrollments/${enrollmentId}`)
        this.enrollments = this.enrollments.filter(e => e.id !== enrollmentId)

        if (this.currentEnrollment?.id === enrollmentId) {
          this.currentEnrollment = null
          this.currentProgress = null
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cancelar inscripción'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Mark topic as completed
    async markTopicCompleted(enrollmentId: number, topicId: number): Promise<TopicProgress> {
      try {
        const response = await api.post(`/enrollments/${enrollmentId}/topics/${topicId}/complete`)

        // Update local enrollment progress
        const enrollmentIndex = this.enrollments.findIndex(e => e.id === enrollmentId)
        if (enrollmentIndex !== -1) {
          this.enrollments[enrollmentIndex].progress_percentage = response.data.enrollment_progress
        }

        if (this.currentEnrollment?.id === enrollmentId) {
          this.currentEnrollment.progress_percentage = response.data.enrollment_progress
        }

        return response.data.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al marcar tema como completado'
        throw error
      }
    },

    // Update watch progress for a topic
    async updateTopicProgress(enrollmentId: number, topicId: number, positionSeconds: number, watchedSeconds: number) {
      try {
        const response = await api.post(`/enrollments/${enrollmentId}/topics/${topicId}/progress`, {
          position_seconds: positionSeconds,
          watched_seconds: watchedSeconds,
        })
        return response.data.data
      } catch (error: any) {
        // Silent fail for progress updates
        console.error('Error updating progress:', error)
      }
    },

    // Check access to a course
    async checkCourseAccess(courseId: number): Promise<CourseAccessDetails> {
      try {
        const response = await api.get(`/enrollments/check-access/${courseId}`)
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al verificar acceso'
        throw error
      }
    },

    // Find enrollment by course ID
    getEnrollmentByCourseId(courseId: number): CourseEnrollment | undefined {
      return this.enrollments.find(e => e.course?.id === courseId)
    },

    // Clear current enrollment
    clearCurrentEnrollment() {
      this.currentEnrollment = null
      this.currentProgress = null
    },

    clearError() {
      this.error = null
    },
  },

  persist: {
    key: 'tenanta-enrollments',
    storage: localStorage,
    paths: ['enrollments'],
  },
})
