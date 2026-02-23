<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useSubscriptionsStore } from '@/stores/subscriptions'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const router = useRouter()
const subscriptionsStore = useSubscriptionsStore()

const loading = ref(true)
const actionLoading = ref(false)
const showCancelDialog = ref(false)
const cancelImmediately = ref(false)

const subscription = computed(() => subscriptionsStore.currentSubscription)
const plan = computed(() => subscription.value?.plan)

const statusColor = computed(() => {
  const colors: Record<string, string> = {
    active: 'success',
    trial: 'info',
    past_due: 'warning',
    cancelled: 'error',
    paused: 'grey',
    expired: 'error',
  }
  return colors[subscription.value?.status || ''] || 'grey'
})

const statusText = computed(() => {
  const texts: Record<string, string> = {
    active: 'Activa',
    trial: 'Período de prueba',
    past_due: 'Pago pendiente',
    cancelled: 'Cancelada',
    paused: 'Pausada',
    expired: 'Expirada',
  }
  return texts[subscription.value?.status || ''] || subscription.value?.status
})

async function loadSubscription() {
  loading.value = true
  try {
    await subscriptionsStore.fetchCurrentSubscription()
    await subscriptionsStore.fetchSubscriptions()
  } finally {
    loading.value = false
  }
}

async function handleCancel() {
  actionLoading.value = true
  try {
    await subscriptionsStore.cancelSubscription(subscription.value!.id, cancelImmediately.value)
    showCancelDialog.value = false
  } finally {
    actionLoading.value = false
  }
}

async function handleReactivate() {
  actionLoading.value = true
  try {
    await subscriptionsStore.reactivateSubscription(subscription.value!.id)
  } finally {
    actionLoading.value = false
  }
}

function formatDate(dateString: string | null): string {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('es-AR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function formatPrice(price: number, currency: string): string {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: currency || 'ARS',
  }).format(price)
}

onMounted(() => {
  loadSubscription()
})
</script>

<template>
  <VContainer class="py-8">
    <VRow>
      <VCol cols="12" md="8" offset-md="2">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-h4 font-weight-bold">Mi suscripción</h1>
          <p class="text-body-1 text-medium-emphasis">
            Administra tu plan y facturación
          </p>
        </div>

        <!-- Loading -->
        <VSkeletonLoader v-if="loading" type="card" />

        <!-- No Subscription -->
        <VCard v-else-if="!subscription" class="text-center pa-8">
          <VIcon icon="mdi-credit-card-off" size="64" color="grey" />
          <h2 class="text-h6 mt-4">No tienes una suscripción activa</h2>
          <p class="text-body-2 text-medium-emphasis mt-2">
            Suscríbete para acceder a todos los cursos
          </p>
          <VBtn
            color="primary"
            class="mt-4"
            :to="{ name: 'subscription-plans' }"
          >
            Ver planes
          </VBtn>
        </VCard>

        <!-- Subscription Details -->
        <template v-else>
          <!-- Current Plan Card -->
          <VCard class="mb-6">
            <VCardText class="pa-6">
              <VRow align="center">
                <VCol cols="12" md="6">
                  <div class="d-flex align-center">
                    <VAvatar color="primary" variant="tonal" size="56" class="me-4">
                      <VIcon icon="mdi-crown" size="28" />
                    </VAvatar>
                    <div>
                      <div class="text-h5 font-weight-bold">
                        {{ plan?.name }}
                      </div>
                      <VChip
                        :color="statusColor"
                        size="small"
                        class="mt-1"
                      >
                        {{ statusText }}
                      </VChip>
                    </div>
                  </div>
                </VCol>
                <VCol cols="12" md="6" class="text-md-end">
                  <div class="text-h4 font-weight-bold">
                    {{ formatPrice(subscription.amount, subscription.currency) }}
                  </div>
                  <div class="text-body-2 text-medium-emphasis">
                    {{ plan?.billing_cycle_label }}
                  </div>
                </VCol>
              </VRow>
            </VCardText>

            <VDivider />

            <VCardText class="pa-6">
              <VRow>
                <VCol cols="6" md="3">
                  <div class="text-caption text-medium-emphasis">Inicio</div>
                  <div class="text-body-1 font-weight-medium">
                    {{ formatDate(subscription.starts_at) }}
                  </div>
                </VCol>

                <VCol cols="6" md="3">
                  <div class="text-caption text-medium-emphasis">Próximo cobro</div>
                  <div class="text-body-1 font-weight-medium">
                    {{ formatDate(subscription.next_payment_at) }}
                  </div>
                </VCol>

                <VCol cols="6" md="3">
                  <div class="text-caption text-medium-emphasis">Método de pago</div>
                  <div class="text-body-1 font-weight-medium">
                    {{ subscription.payment_method || 'No configurado' }}
                  </div>
                </VCol>

                <VCol cols="6" md="3">
                  <div class="text-caption text-medium-emphasis">Días restantes</div>
                  <div class="text-body-1 font-weight-medium">
                    {{ subscription.days_remaining ?? 'Ilimitado' }}
                  </div>
                </VCol>
              </VRow>
            </VCardText>

            <!-- Actions -->
            <VDivider />

            <VCardActions class="pa-4">
              <VBtn
                variant="text"
                :to="{ name: 'subscription-plans' }"
              >
                Cambiar plan
              </VBtn>

              <VSpacer />

              <VBtn
                v-if="subscription.is_cancelled"
                color="primary"
                :loading="actionLoading"
                @click="handleReactivate"
              >
                Reactivar suscripción
              </VBtn>

              <VBtn
                v-else
                color="error"
                variant="text"
                @click="showCancelDialog = true"
              >
                Cancelar suscripción
              </VBtn>
            </VCardActions>
          </VCard>

          <!-- Plan Features -->
          <VCard class="mb-6">
            <VCardTitle>Beneficios de tu plan</VCardTitle>
            <VCardText>
              <VList>
                <VListItem
                  v-for="(feature, index) in plan?.features"
                  :key="index"
                >
                  <template #prepend>
                    <VIcon icon="mdi-check-circle" color="success" />
                  </template>
                  <VListItemTitle>{{ feature }}</VListItemTitle>
                </VListItem>
              </VList>
            </VCardText>
          </VCard>

          <!-- Billing History -->
          <VCard>
            <VCardTitle>Historial de pagos</VCardTitle>
            <VCardText>
              <VTable>
                <thead>
                  <tr>
                    <th>Fecha</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Estado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="subscription.last_payment_at">
                    <td>{{ formatDate(subscription.last_payment_at) }}</td>
                    <td>{{ plan?.name }} - {{ plan?.billing_cycle_label }}</td>
                    <td>{{ formatPrice(subscription.amount, subscription.currency) }}</td>
                    <td>
                      <VChip size="small" color="success">Pagado</VChip>
                    </td>
                  </tr>
                  <tr v-else>
                    <td colspan="4" class="text-center text-medium-emphasis py-4">
                      No hay pagos registrados
                    </td>
                  </tr>
                </tbody>
              </VTable>
            </VCardText>
          </VCard>
        </template>
      </VCol>
    </VRow>

    <!-- Cancel Dialog -->
    <VDialog v-model="showCancelDialog" max-width="500">
      <VCard>
        <VCardTitle class="text-h5">
          ¿Cancelar suscripción?
        </VCardTitle>

        <VCardText>
          <p class="mb-4">
            ¿Estás seguro de que deseas cancelar tu suscripción?
          </p>

          <VRadioGroup v-model="cancelImmediately">
            <VRadio
              :value="false"
              label="Cancelar al final del período de facturación"
            />
            <VRadio
              :value="true"
              label="Cancelar inmediatamente"
              color="error"
            />
          </VRadioGroup>

          <VAlert
            v-if="cancelImmediately"
            type="warning"
            variant="tonal"
            class="mt-4"
          >
            Perderás el acceso a los cursos inmediatamente.
          </VAlert>
        </VCardText>

        <VCardActions>
          <VSpacer />
          <VBtn
            variant="text"
            @click="showCancelDialog = false"
          >
            Volver
          </VBtn>
          <VBtn
            color="error"
            :loading="actionLoading"
            @click="handleCancel"
          >
            Confirmar cancelación
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>
