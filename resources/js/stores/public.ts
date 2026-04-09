import { defineStore } from 'pinia'
import api from '@/api'

interface PublicState {
  branding: any | null
  tenantName: string
  loading: boolean
}

export const usePublicStore = defineStore('public', {
  state: (): PublicState => ({
    branding: null,
    tenantName: '',
    loading: false
  }),

  actions: {
    async fetchBranding() {
      this.loading = true
      try {
        const { data } = await api.get('/public/branding')
        this.branding = data.data
        this.tenantName = data.data.tenant_name
      } finally {
        this.loading = false
      }
    },

    async fetchBrandingBySlug(slug: string) {
      this.loading = true
      try {
        const { data } = await api.get(`/public/branding/${slug}`)
        this.branding = data.data
        this.tenantName = data.data.tenant_name
        return data.data
      } finally {
        this.loading = false
      }
    },

    async sendInquiry(data: any) {
      await api.post('/public/inquiry', data)
    },

    async fetchFeaturedCourses(): Promise<any[]> {
      try {
        const { data } = await api.get('/public/courses', {
          params: { featured: true, limit: 3 }
        })
        return data.data
      } catch {
        return []
      }
    },

    async fetchPlans(): Promise<any[]> {
      const { data } = await api.get('/public/plans')
      return data.data
    }
  }
})
