import { defineStore } from 'pinia'
import api from '@/api'

interface User {
  id: number
  name: string
  email: string
  tenant_id: number
  tenant?: {
    id: number
    name: string
  }
}

interface AuthState {
  user: User | null
  token: string | null
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    tenantId: (state) => state.user?.tenant_id,
    userName: (state) => state.user?.name,
  },

  actions: {
    async login(email: string, password: string) {
      const response = await api.post('/auth/login', { email, password })

      if (response.data.success) {
        this.token = response.data.data.token
        this.user = response.data.data.user
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }

      return response.data
    },

    async register(data: {
      tenant_name: string
      name: string
      email: string
      password: string
      password_confirmation: string
    }) {
      const response = await api.post('/auth/register', data)

      if (response.data.success) {
        this.token = response.data.data.token
        this.user = response.data.data.user
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }

      return response.data
    },

    async fetchUser() {
      if (!this.token) return null

      api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      const response = await api.get('/auth/me')

      if (response.data.success) {
        this.user = response.data.data
      }

      return response.data
    },

    async logout() {
      try {
        await api.post('/auth/logout')
      } finally {
        this.user = null
        this.token = null
        delete api.defaults.headers.common['Authorization']
      }
    },

    async refreshToken() {
      const response = await api.post('/auth/refresh')

      if (response.data.success) {
        this.token = response.data.data.token
        api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
      }

      return response.data
    },
  },

  persist: {
    key: 'tenanta-auth',
    storage: localStorage,
    paths: ['token', 'user'],
  },
})
