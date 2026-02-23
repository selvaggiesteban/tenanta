import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from '@/plugins/axios'
import type {
  EmailTemplate,
  EmailTemplateListItem,
  EmailCampaign,
  CampaignListItem,
  CampaignDetailedStats,
  EmailRecipient,
  RecipientListItem,
  EmailList,
  EmailListItem,
  ListSubscriber,
  SubscriberListItem,
  EmailUnsubscribe,
  UnsubscribeReason,
  UnsubscribeStats,
  CreateTemplateData,
  CreateCampaignData,
  AddRecipientsData,
  CreateListData,
  AddSubscribersData,
} from '@/types/marketing'

export const useMarketingStore = defineStore('marketing', () => {
  // State
  const templates = ref<EmailTemplateListItem[]>([])
  const currentTemplate = ref<EmailTemplate | null>(null)
  const campaigns = ref<CampaignListItem[]>([])
  const currentCampaign = ref<EmailCampaign | null>(null)
  const currentCampaignStats = ref<CampaignDetailedStats | null>(null)
  const recipients = ref<RecipientListItem[]>([])
  const lists = ref<EmailListItem[]>([])
  const currentList = ref<EmailList | null>(null)
  const subscribers = ref<SubscriberListItem[]>([])
  const unsubscribes = ref<EmailUnsubscribe[]>([])
  const unsubscribeReasons = ref<UnsubscribeReason[]>([])
  const unsubscribeStats = ref<UnsubscribeStats | null>(null)
  const categories = ref<string[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  // Pagination state
  const pagination = ref({
    page: 1,
    perPage: 15,
    total: 0,
    lastPage: 1,
  })

  // Template Actions
  async function fetchTemplates(params: Record<string, any> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get('/marketing/templates', { params })
      templates.value = response.data.data
      if (response.data.meta) {
        pagination.value = {
          page: response.data.meta.current_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
          lastPage: response.data.meta.last_page,
        }
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar plantillas'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchTemplate(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get(`/marketing/templates/${id}`)
      currentTemplate.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar plantilla'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function createTemplate(data: CreateTemplateData) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.post('/marketing/templates', data)
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al crear plantilla'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateTemplate(id: number, data: Partial<CreateTemplateData>) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.put(`/marketing/templates/${id}`, data)
      currentTemplate.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al actualizar plantilla'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteTemplate(id: number) {
    loading.value = true
    try {
      await axios.delete(`/marketing/templates/${id}`)
      templates.value = templates.value.filter(t => t.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al eliminar plantilla'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function duplicateTemplate(id: number) {
    loading.value = true
    try {
      const response = await axios.post(`/marketing/templates/${id}/duplicate`)
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al duplicar plantilla'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function previewTemplate(id: number, mergeFields: Record<string, any> = {}) {
    try {
      const response = await axios.post(`/marketing/templates/${id}/preview`, { merge_fields: mergeFields })
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al previsualizar'
      throw e
    }
  }

  async function fetchCategories() {
    try {
      const response = await axios.get('/marketing/templates-categories')
      categories.value = response.data.data
    } catch (e) {
      // Ignore error, categories are optional
    }
  }

  // Campaign Actions
  async function fetchCampaigns(params: Record<string, any> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get('/marketing/campaigns', { params })
      campaigns.value = response.data.data
      if (response.data.meta) {
        pagination.value = {
          page: response.data.meta.current_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
          lastPage: response.data.meta.last_page,
        }
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar campañas'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchCampaign(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get(`/marketing/campaigns/${id}`)
      currentCampaign.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar campaña'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function createCampaign(data: CreateCampaignData) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.post('/marketing/campaigns', data)
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al crear campaña'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateCampaign(id: number, data: Partial<CreateCampaignData>) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.put(`/marketing/campaigns/${id}`, data)
      currentCampaign.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al actualizar campaña'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteCampaign(id: number) {
    loading.value = true
    try {
      await axios.delete(`/marketing/campaigns/${id}`)
      campaigns.value = campaigns.value.filter(c => c.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al eliminar campaña'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function duplicateCampaign(id: number) {
    loading.value = true
    try {
      const response = await axios.post(`/marketing/campaigns/${id}/duplicate`)
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al duplicar campaña'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchCampaignRecipients(campaignId: number, params: Record<string, any> = {}) {
    loading.value = true
    try {
      const response = await axios.get(`/marketing/campaigns/${campaignId}/recipients`, { params })
      recipients.value = response.data.data
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar destinatarios'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function addCampaignRecipients(campaignId: number, data: AddRecipientsData) {
    loading.value = true
    try {
      const response = await axios.post(`/marketing/campaigns/${campaignId}/recipients`, data)
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al agregar destinatarios'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function removeCampaignRecipient(campaignId: number, email: string) {
    loading.value = true
    try {
      await axios.delete(`/marketing/campaigns/${campaignId}/recipients/${encodeURIComponent(email)}`)
      recipients.value = recipients.value.filter(r => r.email !== email)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al eliminar destinatario'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function scheduleCampaign(campaignId: number, scheduledAt: string) {
    loading.value = true
    try {
      const response = await axios.post(`/marketing/campaigns/${campaignId}/schedule`, {
        scheduled_at: scheduledAt,
      })
      currentCampaign.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al programar campaña'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function cancelSchedule(campaignId: number) {
    loading.value = true
    try {
      const response = await axios.post(`/marketing/campaigns/${campaignId}/cancel-schedule`)
      currentCampaign.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cancelar programación'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function sendCampaign(campaignId: number) {
    loading.value = true
    try {
      const response = await axios.post(`/marketing/campaigns/${campaignId}/send`)
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al enviar campaña'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchCampaignStats(campaignId: number) {
    loading.value = true
    try {
      const response = await axios.get(`/marketing/campaigns/${campaignId}/stats`)
      currentCampaignStats.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar estadísticas'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function previewCampaign(id: number, mergeFields: Record<string, any> = {}) {
    try {
      const response = await axios.post(`/marketing/campaigns/${id}/preview`, { merge_fields: mergeFields })
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al previsualizar'
      throw e
    }
  }

  // List Actions
  async function fetchLists(params: Record<string, any> = {}) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get('/marketing/lists', { params })
      lists.value = response.data.data
      if (response.data.meta) {
        pagination.value = {
          page: response.data.meta.current_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
          lastPage: response.data.meta.last_page,
        }
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar listas'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchList(id: number) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.get(`/marketing/lists/${id}`)
      currentList.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar lista'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function createList(data: CreateListData) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.post('/marketing/lists', data)
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al crear lista'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateList(id: number, data: Partial<CreateListData>) {
    loading.value = true
    error.value = null
    try {
      const response = await axios.put(`/marketing/lists/${id}`, data)
      currentList.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al actualizar lista'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteList(id: number) {
    loading.value = true
    try {
      await axios.delete(`/marketing/lists/${id}`)
      lists.value = lists.value.filter(l => l.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al eliminar lista'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchListSubscribers(listId: number, params: Record<string, any> = {}) {
    loading.value = true
    try {
      const response = await axios.get(`/marketing/lists/${listId}/subscribers`, { params })
      subscribers.value = response.data.data
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar suscriptores'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function addListSubscribers(listId: number, data: AddSubscribersData) {
    loading.value = true
    try {
      const response = await axios.post(`/marketing/lists/${listId}/subscribers`, data)
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al agregar suscriptores'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function importListSubscribers(listId: number, file: File) {
    loading.value = true
    try {
      const formData = new FormData()
      formData.append('file', file)
      const response = await axios.post(`/marketing/lists/${listId}/import`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al importar suscriptores'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function removeListSubscriber(listId: number, subscriberId: number) {
    loading.value = true
    try {
      await axios.delete(`/marketing/lists/${listId}/subscribers/${subscriberId}`)
      subscribers.value = subscribers.value.filter(s => s.id !== subscriberId)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al eliminar suscriptor'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function syncDynamicList(listId: number) {
    loading.value = true
    try {
      const response = await axios.post(`/marketing/lists/${listId}/sync`)
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al sincronizar lista'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function exportList(listId: number) {
    try {
      const response = await axios.get(`/marketing/lists/${listId}/export`)
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al exportar lista'
      throw e
    }
  }

  // Unsubscribe Actions
  async function fetchUnsubscribes(params: Record<string, any> = {}) {
    loading.value = true
    try {
      const response = await axios.get('/marketing/unsubscribes', { params })
      unsubscribes.value = response.data.data
      if (response.data.meta) {
        pagination.value = {
          page: response.data.meta.current_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
          lastPage: response.data.meta.last_page,
        }
      }
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar desuscripciones'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function resubscribe(email: string) {
    loading.value = true
    try {
      const response = await axios.post('/marketing/unsubscribes/resubscribe', { email })
      unsubscribes.value = unsubscribes.value.filter(u => u.email !== email)
      return response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al reactivar email'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function fetchUnsubscribeReasons() {
    try {
      const response = await axios.get('/marketing/unsubscribes/reasons')
      unsubscribeReasons.value = response.data.data
    } catch (e) {
      // Ignore error
    }
  }

  async function fetchUnsubscribeStats() {
    try {
      const response = await axios.get('/marketing/unsubscribes/stats')
      unsubscribeStats.value = response.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error al cargar estadísticas'
      throw e
    }
  }

  // Computed
  const draftCampaigns = computed(() =>
    campaigns.value.filter(c => c.status === 'draft')
  )

  const scheduledCampaigns = computed(() =>
    campaigns.value.filter(c => c.status === 'scheduled')
  )

  const sentCampaigns = computed(() =>
    campaigns.value.filter(c => c.status === 'sent')
  )

  const activeLists = computed(() =>
    lists.value.filter(l => l.is_active)
  )

  const activeTemplates = computed(() =>
    templates.value.filter(t => t.is_active)
  )

  // Reset
  function resetState() {
    templates.value = []
    currentTemplate.value = null
    campaigns.value = []
    currentCampaign.value = null
    currentCampaignStats.value = null
    recipients.value = []
    lists.value = []
    currentList.value = null
    subscribers.value = []
    unsubscribes.value = []
    error.value = null
  }

  return {
    // State
    templates,
    currentTemplate,
    campaigns,
    currentCampaign,
    currentCampaignStats,
    recipients,
    lists,
    currentList,
    subscribers,
    unsubscribes,
    unsubscribeReasons,
    unsubscribeStats,
    categories,
    loading,
    error,
    pagination,

    // Template Actions
    fetchTemplates,
    fetchTemplate,
    createTemplate,
    updateTemplate,
    deleteTemplate,
    duplicateTemplate,
    previewTemplate,
    fetchCategories,

    // Campaign Actions
    fetchCampaigns,
    fetchCampaign,
    createCampaign,
    updateCampaign,
    deleteCampaign,
    duplicateCampaign,
    fetchCampaignRecipients,
    addCampaignRecipients,
    removeCampaignRecipient,
    scheduleCampaign,
    cancelSchedule,
    sendCampaign,
    fetchCampaignStats,
    previewCampaign,

    // List Actions
    fetchLists,
    fetchList,
    createList,
    updateList,
    deleteList,
    fetchListSubscribers,
    addListSubscribers,
    importListSubscribers,
    removeListSubscriber,
    syncDynamicList,
    exportList,

    // Unsubscribe Actions
    fetchUnsubscribes,
    resubscribe,
    fetchUnsubscribeReasons,
    fetchUnsubscribeStats,

    // Computed
    draftCampaigns,
    scheduledCampaigns,
    sentCampaigns,
    activeLists,
    activeTemplates,

    // Reset
    resetState,
  }
})
