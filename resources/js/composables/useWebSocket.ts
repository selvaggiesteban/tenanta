import { ref, onMounted, onUnmounted } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import { useAuthStore } from '@/stores/auth'

declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo
  }
}

let echoInstance: Echo | null = null

export function useWebSocket() {
  const authStore = useAuthStore()
  const isConnected = ref(false)
  const notifications = ref<any[]>([])

  const initEcho = () => {
    if (echoInstance) return echoInstance

    window.Pusher = Pusher

    echoInstance = new Echo({
      broadcaster: 'reverb',
      key: import.meta.env.VITE_REVERB_APP_KEY,
      wsHost: import.meta.env.VITE_REVERB_HOST,
      wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
      wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
      forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: '/api/v1/broadcasting/auth',
      auth: {
        headers: {
          Authorization: `Bearer ${authStore.token}`,
        },
      },
    })

    echoInstance.connector.pusher.connection.bind('connected', () => {
      isConnected.value = true
    })

    echoInstance.connector.pusher.connection.bind('disconnected', () => {
      isConnected.value = false
    })

    return echoInstance
  }

  const subscribeToTenant = (callback: (event: string, data: any) => void) => {
    const echo = initEcho()
    const tenantId = authStore.tenantId

    if (!tenantId) return

    echo.private(`tenant.${tenantId}`)
      .listen('.task.updated', (data: any) => callback('task.updated', data))
      .listen('.lead.moved', (data: any) => callback('lead.moved', data))
      .listen('.ticket.updated', (data: any) => callback('ticket.updated', data))
  }

  const subscribeToUser = (callback: (data: any) => void) => {
    const echo = initEcho()
    const userId = authStore.user?.id

    if (!userId) return

    echo.private(`user.${userId}`)
      .listen('.notification.created', (data: any) => {
        notifications.value.unshift(data)
        callback(data)
      })
  }

  const subscribeToProject = (projectId: number, callback: (event: string, data: any) => void) => {
    const echo = initEcho()

    echo.private(`project.${projectId}`)
      .listen('.task.updated', (data: any) => callback('task.updated', data))
  }

  const unsubscribeFromTenant = () => {
    const tenantId = authStore.tenantId
    if (echoInstance && tenantId) {
      echoInstance.leave(`tenant.${tenantId}`)
    }
  }

  const unsubscribeFromUser = () => {
    const userId = authStore.user?.id
    if (echoInstance && userId) {
      echoInstance.leave(`user.${userId}`)
    }
  }

  const unsubscribeFromProject = (projectId: number) => {
    if (echoInstance) {
      echoInstance.leave(`project.${projectId}`)
    }
  }

  const disconnect = () => {
    if (echoInstance) {
      echoInstance.disconnect()
      echoInstance = null
      isConnected.value = false
    }
  }

  return {
    isConnected,
    notifications,
    initEcho,
    subscribeToTenant,
    subscribeToUser,
    subscribeToProject,
    unsubscribeFromTenant,
    unsubscribeFromUser,
    unsubscribeFromProject,
    disconnect,
  }
}
