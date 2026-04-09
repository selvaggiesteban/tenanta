import { defineStore } from 'pinia'
import api from '@/api'

interface BrandingState {
  config: any | null
  loading: boolean
  saving: boolean
  error: string | null
}

export const useBrandingStore = defineStore('branding', {
  state: (): BrandingState => ({
    config: null,
    loading: false,
    saving: false,
    error: null,
  }),

  actions: {
    async fetchConfig() {
      this.loading = true
      this.error = null
      try {
        const { data } = await api.get('/branding')
        this.config = data.data
        return data.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar configuración de marca'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateConfig(payload: any) {
      this.saving = true
      this.error = null
      try {
        const { data } = await api.put('/branding', payload)
        this.config = data.data
        return data.data
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al actualizar configuración'
        throw error
      } finally {
        this.saving = false
      }
    },
  },
})
