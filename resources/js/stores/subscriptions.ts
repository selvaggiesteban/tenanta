import { defineStore } from 'pinia'
import api from '@/api'
import type { Subscription, SubscriptionPlan } from '@/types/courses'

interface SubscriptionsState {
  plans: SubscriptionPlan[]
  featuredPlans: SubscriptionPlan[]
  subscriptions: Subscription[]
  currentSubscription: Subscription | null
  loading: boolean
  error: string | null
}

export const useSubscriptionsStore = defineStore('subscriptions', {
  state: (): SubscriptionsState => ({
    plans: [],
    featuredPlans: [],
    subscriptions: [],
    currentSubscription: null,
    loading: false,
    error: null,
  }),

  getters: {
    activePlans: (state) => state.plans.filter(p => p.is_active),
    hasActiveSubscription: (state) => state.currentSubscription?.is_active ?? false,
    isOnTrial: (state) => state.currentSubscription?.is_on_trial ?? false,
  },

  actions: {
    // Fetch available plans (public)
    async fetchPlans() {
      this.loading = true
      this.error = null

      try {
        const response = await api.get('/public/plans')
        this.plans = response.data.data || []
        return this.plans
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar planes'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch featured plans
    async fetchFeaturedPlans() {
      try {
        const response = await api.get('/public/plans/featured')
        this.featuredPlans = response.data.data || []
        return this.featuredPlans
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar planes destacados'
        throw error
      }
    },

    // Fetch user subscriptions
    async fetchSubscriptions(status?: string) {
      this.loading = true
      this.error = null

      try {
        const params: any = {}
        if (status) params.status = status

        const response = await api.get('/subscriptions', { params })
        this.subscriptions = response.data.data || []
        return this.subscriptions
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar suscripciones'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Fetch current active subscription
    async fetchCurrentSubscription() {
      this.loading = true
      this.error = null

      try {
        const response = await api.get('/subscriptions/current')
        this.currentSubscription = response.data.data
        return this.currentSubscription
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cargar suscripción actual'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Subscribe to a plan
    async subscribe(planId: number, paymentData?: {
      payment_provider?: string
      payment_provider_id?: string
      payment_method?: string
    }) {
      this.loading = true
      this.error = null

      try {
        const data = {
          plan_id: planId,
          ...paymentData,
        }

        const response = await api.post('/subscriptions', data)
        const subscription = response.data.data
        this.subscriptions.unshift(subscription)
        this.currentSubscription = subscription
        return subscription
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al suscribirse'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Cancel subscription
    async cancelSubscription(subscriptionId: number, immediately: boolean = false) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post(`/subscriptions/${subscriptionId}/cancel`, { immediately })
        const updatedSubscription = response.data.data

        const index = this.subscriptions.findIndex(s => s.id === subscriptionId)
        if (index !== -1) {
          this.subscriptions[index] = updatedSubscription
        }

        if (this.currentSubscription?.id === subscriptionId) {
          this.currentSubscription = updatedSubscription
        }

        return updatedSubscription
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cancelar suscripción'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Reactivate cancelled subscription
    async reactivateSubscription(subscriptionId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post(`/subscriptions/${subscriptionId}/reactivate`)
        const updatedSubscription = response.data.data

        const index = this.subscriptions.findIndex(s => s.id === subscriptionId)
        if (index !== -1) {
          this.subscriptions[index] = updatedSubscription
        }

        if (this.currentSubscription?.id === subscriptionId) {
          this.currentSubscription = updatedSubscription
        }

        return updatedSubscription
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al reactivar suscripción'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Change subscription plan
    async changePlan(subscriptionId: number, newPlanId: number) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post(`/subscriptions/${subscriptionId}/change-plan`, {
          plan_id: newPlanId,
        })
        const updatedSubscription = response.data.data

        const index = this.subscriptions.findIndex(s => s.id === subscriptionId)
        if (index !== -1) {
          this.subscriptions[index] = updatedSubscription
        }

        if (this.currentSubscription?.id === subscriptionId) {
          this.currentSubscription = updatedSubscription
        }

        return updatedSubscription
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cambiar plan'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Admin: Create plan
    async createPlan(data: Partial<SubscriptionPlan>) {
      this.loading = true
      this.error = null

      try {
        const response = await api.post('/subscriptions/plans', data)
        const plan = response.data.data
        this.plans.push(plan)
        return plan
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al crear plan'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Admin: Update plan
    async updatePlan(planId: number, data: Partial<SubscriptionPlan>) {
      this.loading = true
      this.error = null

      try {
        const response = await api.put(`/subscriptions/plans/${planId}`, data)
        const updatedPlan = response.data.data

        const index = this.plans.findIndex(p => p.id === planId)
        if (index !== -1) {
          this.plans[index] = updatedPlan
        }

        return updatedPlan
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al actualizar plan'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Admin: Delete plan
    async deletePlan(planId: number) {
      this.loading = true
      this.error = null

      try {
        await api.delete(`/subscriptions/plans/${planId}`)
        this.plans = this.plans.filter(p => p.id !== planId)
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al eliminar plan'
        throw error
      } finally {
        this.loading = false
      }
    },

    // Admin: Toggle plan active status
    async togglePlanActive(planId: number) {
      try {
        const response = await api.post(`/subscriptions/plans/${planId}/toggle-active`)
        const updatedPlan = response.data.data

        const index = this.plans.findIndex(p => p.id === planId)
        if (index !== -1) {
          this.plans[index] = updatedPlan
        }

        return updatedPlan
      } catch (error: any) {
        this.error = error.response?.data?.message || 'Error al cambiar estado del plan'
        throw error
      }
    },

    clearError() {
      this.error = null
    },
  },

  persist: {
    key: 'tenanta-subscriptions',
    storage: localStorage,
    paths: ['currentSubscription'],
  },
})
