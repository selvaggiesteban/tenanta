import { defineStore } from 'pinia'
import api from '@/api'
import type {
  CourseTest,
  TestAttempt,
  TestAttemptState,
} from '@/types/courses'

interface TestsState {
  tests: CourseTest[]
  currentTest: CourseTest | null
  currentAttempt: TestAttempt | null
  attemptState: TestAttemptState | null
  attempts: TestAttempt[]
  loading: boolean
  error: string | null
}

export const useTestsStore = defineStore('tests', {
  state: (): TestsState => ({
    tests: [],
    currentTest: null,
    currentAttempt: null,
    attemptState: null,
    attempts: [],
    loading: false,
    error: null,
  }),

  getters: {
    isAttemptInProgress: (state) => state.currentAttempt?.is_in_progress ?? false,
    timeRemaining: (state) => state.attemptState?.time_remaining_seconds ?? null,
    answeredCount: (state) => state.attemptState?.answered_questions ?? 0,
    totalQuestions: (state) => state.attemptState?.total_questions ?? 0,
  },

  actions: {
    // Fetch tests for a course
    async fetchCourseTests(courseId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/courses/${courseId}/tests`)
        this.tests = response.data.data || []
        return this.tests
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar exámenes'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Start a test attempt
    async startAttempt(testId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post(`/test-attempts/tests/${testId}/start`)
        this.attemptState = response.data.data
        this.currentAttempt = {
          id: response.data.data.attempt_id,
          test_id: testId,
          is_in_progress: true,
          is_completed: false,
        } as TestAttempt

        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al iniciar examen'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get current attempt state
    async fetchAttemptState(attemptId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/test-attempts/${attemptId}/state`)

        if (response.data.completed) {
          this.currentAttempt = response.data.data
          this.attemptState = null
        } else {
          this.attemptState = response.data.data
        }

        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar estado del examen'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Save progress (auto-save)
    async saveProgress(attemptId: number, answers: Record<number, number[]>) {
      try {
        const response = await api.post(`/test-attempts/${attemptId}/save`, { answers })
        return response.data
      } catch (error: any) {
        console.error('Error saving progress:', error)
        // Don't throw - silent fail for auto-save
      }
    },

    // Submit test
    async submitAttempt(attemptId: number, answers: Record<number, number[]>) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post(`/test-attempts/${attemptId}/submit`, { answers })
        this.currentAttempt = response.data.data
        this.attemptState = null
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al enviar examen'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get attempt results
    async fetchAttemptResults(attemptId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/test-attempts/${attemptId}/results`)
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar resultados'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get test history for user
    async fetchTestHistory(testId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/test-attempts/tests/${testId}/history`)
        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar historial'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch user attempts
    async fetchAttempts(testId?: number) {
      this.loading = true
      this.error = null

      try {
        const params: any = {}
        if (testId) params.test_id = testId

        const response = await api.get('/test-attempts', { params })
        this.attempts = response.data.data || []
        return this.attempts
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar intentos'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update answer locally
    updateAnswer(questionId: number, optionIds: number[]) {
      if (this.attemptState) {
        const question = this.attemptState.questions.find(q => q.id === questionId)
        if (question) {
          question.selected = optionIds
          this.attemptState.answered_questions = this.attemptState.questions.filter(
            q => q.selected && q.selected.length > 0
          ).length
        }
      }
    },

    // Get current answers as object
    getCurrentAnswers(): Record<number, number[]> {
      if (!this.attemptState) return {}

      const answers: Record<number, number[]> = {}
      for (const question of this.attemptState.questions) {
        if (question.selected) {
          answers[question.id] = question.selected
        }
      }
      return answers
    },

    // Clear current attempt
    clearAttempt() {
      this.currentAttempt = null
      this.attemptState = null
    },

    clearError() {
      this.error = null
    },
  },
})
