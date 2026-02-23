import { defineStore } from 'pinia'
import api from '@/api'
import type {
  Course,
  CourseBlock,
  CourseTopic,
  CourseFilters,
  PaginatedResponse,
} from '@/types/courses'

interface CoursesState {
  courses: Course[]
  currentCourse: Course | null
  catalogCourses: Course[]
  loading: boolean
  error: string | null
  pagination: {
    currentPage: number
    lastPage: number
    perPage: number
    total: number
  }
  filters: CourseFilters
}

export const useCoursesStore = defineStore('courses', {
  state: (): CoursesState => ({
    courses: [],
    currentCourse: null,
    catalogCourses: [],
    loading: false,
    error: null,
    pagination: {
      currentPage: 1,
      lastPage: 1,
      perPage: 12,
      total: 0,
    },
    filters: {},
  }),

  getters: {
    publishedCourses: (state) => state.courses.filter(c => c.status === 'published'),
    draftCourses: (state) => state.courses.filter(c => c.status === 'draft'),
    freeCourses: (state) => state.catalogCourses.filter(c => c.price === 0),
  },

  actions: {
    // Admin/Manager - Get all courses
    async fetchCourses(filters: CourseFilters = {}) {
      this.loading = true
      this.error = null

      try {
        const params = { ...this.filters, ...filters }
        const response = await api.get('/courses', { params })

        if (response.data.data) {
          this.courses = response.data.data
          if (response.data.meta) {
            this.pagination = {
              currentPage: response.data.meta.current_page,
              lastPage: response.data.meta.last_page,
              perPage: response.data.meta.per_page,
              total: response.data.meta.total,
            }
          }
        }

        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar cursos'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Public - Get course catalog
    async fetchCatalog(filters: CourseFilters = {}) {
      this.loading = true
      this.error = null

      try {
        const params = { ...filters }
        const response = await api.get('/public/courses', { params })

        if (response.data.data) {
          this.catalogCourses = response.data.data
          if (response.data.meta) {
            this.pagination = {
              currentPage: response.data.meta.current_page,
              lastPage: response.data.meta.last_page,
              perPage: response.data.meta.per_page,
              total: response.data.meta.total,
            }
          }
        }

        return response.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar catálogo'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get single course by ID
    async fetchCourse(id: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/courses/${id}`)
        this.currentCourse = response.data.data || response.data
        return this.currentCourse
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Get course by slug (public)
    async fetchCourseBySlug(slug: string) {
      this.loading = true
      this.error = null

      try {
        const response = await api.get(`/public/courses/${slug}`)
        this.currentCourse = response.data.data || response.data
        return this.currentCourse
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Create course
    async createCourse(data: Partial<Course>) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post('/courses', data)
        const newCourse = response.data.data
        this.courses.unshift(newCourse)
        return newCourse
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al crear curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update course
    async updateCourse(id: number, data: Partial<Course>) {
      this.loading = true
      this.error = null

      try {
        const response = await api.put(`/courses/${id}`, data)
        const updatedCourse = response.data.data

        const index = this.courses.findIndex(c => c.id === id)
        if (index !== -1) {
          this.courses[index] = updatedCourse
        }

        if (this.currentCourse?.id === id) {
          this.currentCourse = updatedCourse
        }

        return updatedCourse
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al actualizar curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Delete course
    async deleteCourse(id: number) {
      this.loading = true
      this.error = null

      try {
        await api.delete(`/courses/${id}`)
        this.courses = this.courses.filter(c => c.id !== id)

        if (this.currentCourse?.id === id) {
          this.currentCourse = null
        }
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al eliminar curso'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Publish course
    async publishCourse(id: number) {
      try {
        const response = await api.post(`/courses/${id}/publish`)
        const updatedCourse = response.data.data

        const index = this.courses.findIndex(c => c.id === id)
        if (index !== -1) {
          this.courses[index] = updatedCourse
        }

        if (this.currentCourse?.id === id) {
          this.currentCourse = updatedCourse
        }

        return updatedCourse
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al publicar curso'
        throw error
      }
    },

    // Unpublish course
    async unpublishCourse(id: number) {
      try {
        const response = await api.post(`/courses/${id}/unpublish`)
        const updatedCourse = response.data.data

        const index = this.courses.findIndex(c => c.id === id)
        if (index !== -1) {
          this.courses[index] = updatedCourse
        }

        return updatedCourse
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al despublicar curso'
        throw error
      }
    },

    // Block management
    async createBlock(courseId: number, data: Partial<CourseBlock>) {
      const response = await api.post(`/courses/${courseId}/blocks`, data)
      return response.data.data
    },

    async updateBlock(courseId: number, blockId: number, data: Partial<CourseBlock>) {
      const response = await api.put(`/courses/${courseId}/blocks/${blockId}`, data)
      return response.data.data
    },

    async deleteBlock(courseId: number, blockId: number) {
      await api.delete(`/courses/${courseId}/blocks/${blockId}`)
    },

    async reorderBlocks(courseId: number, blocks: { id: number; sort_order: number }[]) {
      await api.post(`/courses/${courseId}/blocks/reorder`, { blocks })
    },

    // Topic management
    async createTopic(courseId: number, blockId: number, data: Partial<CourseTopic>) {
      const response = await api.post(`/courses/${courseId}/blocks/${blockId}/topics`, data)
      return response.data.data
    },

    async updateTopic(courseId: number, blockId: number, topicId: number, data: Partial<CourseTopic>) {
      const response = await api.put(`/courses/${courseId}/blocks/${blockId}/topics/${topicId}`, data)
      return response.data.data
    },

    async deleteTopic(courseId: number, blockId: number, topicId: number) {
      await api.delete(`/courses/${courseId}/blocks/${blockId}/topics/${topicId}`)
    },

    async reorderTopics(courseId: number, blockId: number, topics: { id: number; sort_order: number }[]) {
      await api.post(`/courses/${courseId}/blocks/${blockId}/topics/reorder`, { topics })
    },

    // Utilities
    setFilters(filters: CourseFilters) {
      this.filters = filters
    },

    clearCurrentCourse() {
      this.currentCourse = null
    },

    clearError() {
      this.error = null
    },

    // Test management
    async startTest(testId: number): Promise<any> {
      this.loading = true
      try {
        const { data } = await api.post(`/tests/${testId}/start`)
        return data.data
      } finally {
        this.loading = false
      }
    },

    async fetchTest(testId: number): Promise<any> {
      this.loading = true
      try {
        const { data } = await api.get(`/tests/${testId}`)
        return data.data
      } finally {
        this.loading = false
      }
    },

    async submitTest(testId: number, attemptId: number, answers: Record<number, any>): Promise<any> {
      this.loading = true
      try {
        const { data } = await api.post(`/tests/${testId}/submit`, {
          attemptId,
          answers
        })
        return data.data
      } finally {
        this.loading = false
      }
    },
  },
})
