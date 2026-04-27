import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/api'
import { echo } from '@/plugins/echo'
import type {
  Channel,
  Conversation,
  Message,
  ChannelType,
} from '@/types/omnichannel'

export const useOmnichannelStore = defineStore('omnichannel', () => {
  const channels = ref<Channel[]>([])
  const conversations = ref<Conversation[]>([])
  const activeConversation = ref<Conversation | null>(null)
  const messages = ref<Message[]>([])
  const cannedResponses = ref<any[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const isSubscribed = ref(false)
  const typingAgents = ref<Record<string, string[]>>({}) // conversationId -> agentNames[]

  // Real-time Actions
  function subscribeToMessages(tenantId: number) {
    if (isSubscribed.value) return

    echo.private(`tenants.${tenantId}.omnichannel`)
      .listen('.message.received', (data: { message: Message }) => {
        const incomingMessage = data.message

        if (activeConversation.value?.id === incomingMessage.conversation_id) {
          if (!messages.value.find(m => m.id === incomingMessage.id)) {
            messages.value.push(incomingMessage)
          }
        }

        const conversationIndex = conversations.value.findIndex(c => c.id === incomingMessage.conversation_id)
        if (conversationIndex !== -1) {
          const conv = conversations.value[conversationIndex]
          conv.last_message_at = incomingMessage.created_at
          conversations.value.splice(conversationIndex, 1)
          conversations.value.unshift(conv)
        } else {
          fetchConversations()
        }
      })
      .listen('.agent.typing', (data: { conversation_id: string, agent_name: string, is_typing: boolean }) => {
        const { conversation_id, agent_name, is_typing } = data
        
        if (!typingAgents.value[conversation_id]) {
          typingAgents.value[conversation_id] = []
        }

        if (is_typing) {
          if (!typingAgents.value[conversation_id].includes(agent_name)) {
            typingAgents.value[conversation_id].push(agent_name)
          }
        } else {
          typingAgents.value[conversation_id] = typingAgents.value[conversation_id].filter(name => name !== agent_name)
        }
      })

    isSubscribed.value = true
  }

  async function sendTypingEvent(conversationId: string, isTyping: boolean) {
    try {
      await api.post('/omnichannel/emit-typing', {
        conversation_id: conversationId,
        is_typing: isTyping
      })
    } catch (e) {
      // Fail silently for presence events
    }
  }

  function unsubscribeFromMessages(tenantId: number) {
    echo.leave(`tenants.${tenantId}.omnichannel`)
    isSubscribed.value = false
  }

  // Channel Actions
  async function fetchChannels() {
    loading.value = true
    try {
      const response = await api.get('/omnichannel/channels')
      channels.value = response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error fetching channels'
    } finally {
      loading.value = false
    }
  }

  async function createChannel(data: Partial<Channel>) {
    loading.value = true
    try {
      const response = await api.post('/omnichannel/channels', data)
      channels.value.push(response.data.data)
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error creating channel'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function updateChannel(id: string, data: Partial<Channel>) {
    loading.value = true
    try {
      const response = await api.put(`/omnichannel/channels/${id}`, data)
      const index = channels.value.findIndex(c => c.id === id)
      if (index !== -1) channels.value[index] = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error updating channel'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteChannel(id: string) {
    loading.value = true
    try {
      await api.delete(`/omnichannel/channels/${id}`)
      channels.value = channels.value.filter(c => c.id !== id)
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error deleting channel'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Canned Responses Actions
  async function fetchCannedResponses() {
    try {
      const response = await api.get('/omnichannel/canned-responses')
      cannedResponses.value = response.data.data
    } catch (e) {
      console.error('Error fetching canned responses', e)
    }
  }

  async function createCannedResponse(data: any) {
    loading.value = true
    try {
      const response = await api.post('/omnichannel/canned-responses', data)
      cannedResponses.value.push(response.data.data)
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error creating canned response'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function deleteCannedResponse(id: number) {
    try {
      await api.delete(`/omnichannel/canned-responses/${id}`)
      cannedResponses.value = cannedResponses.value.filter(r => r.id !== id)
    } catch (e) {
      console.error('Error deleting canned response', e)
    }
  }

  // Inbox Actions
  async function fetchConversations(params: any = {}) {
    loading.value = true
    try {
      const response = await api.get('/omnichannel/conversations', { params })
      conversations.value = response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error fetching inbox'
    } finally {
      loading.value = false
    }
  }

  async function fetchMessages(conversationId: string) {
    loading.value = true
    try {
      const response = await api.get(`/omnichannel/conversations/${conversationId}/messages`)
      messages.value = response.data.data
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error fetching messages'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function sendMessage(data: { conversation_id: string, message: string, type?: 'message' | 'note' }) {
    loading.value = true
    try {
      const response = await api.post('/omnichannel/send', data)
      messages.value.push(response.data.data)
      return response.data.data
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error sending message'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function linkContact(conversationId: string, contactId: number) {
    loading.value = true
    try {
      const response = await api.post(`/omnichannel/conversations/${conversationId}/link-contact`, { contact_id: contactId })
      const updatedConv = response.data.data
      
      // Update in list
      const index = conversations.value.findIndex(c => c.id === conversationId)
      if (index !== -1) conversations.value[index] = updatedConv
      
      // Update active if it's the same
      if (activeConversation.value?.id === conversationId) {
        activeConversation.value = updatedConv
      }
      
      return updatedConv
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error linking contact'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function assignAgent(conversationId: string, userId: number) {
    loading.value = true
    try {
      const response = await api.post(`/omnichannel/conversations/${conversationId}/assign-agent`, { user_id: userId })
      const updatedConv = response.data.data

      const index = conversations.value.findIndex(c => c.id === conversationId)
      if (index !== -1) conversations.value[index] = updatedConv
      
      if (activeConversation.value?.id === conversationId) {
        activeConversation.value = updatedConv
      }
      
      return updatedConv
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error assigning agent'
      throw e
    } finally {
      loading.value = false
    }
  }

  async function getAiSuggestion(conversationId: string) {
    loading.value = true
    try {
      const response = await api.get(`/omnichannel/conversations/${conversationId}/suggest-response`)
      return response.data.suggestion
    } catch (e: any) {
      error.value = e.response?.data?.message || 'Error obteniendo sugerencia IA'
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    channels,
    conversations,
    activeConversation,
    messages,
    cannedResponses,
    loading,
    error,
    isSubscribed,
    typingAgents,
    fetchChannels,
    createChannel,
    updateChannel,
    deleteChannel,
    fetchCannedResponses,
    createCannedResponse,
    deleteCannedResponse,
    fetchConversations,
    fetchMessages,
    sendMessage,
    linkContact,
    assignAgent,
    getAiSuggestion,
    subscribeToMessages,
    unsubscribeFromMessages,
    sendTypingEvent,
  }
})
