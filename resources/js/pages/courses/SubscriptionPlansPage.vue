<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useSubscriptionsStore } from '@/stores/subscriptions'
import { useI18n } from 'vue-i18n'
import type { SubscriptionPlan } from '@/types/courses'

const { t } = useI18n()
const router = useRouter()
const subscriptionsStore = useSubscriptionsStore()

const loading = ref(true)
const subscribing = ref<number | null>(null)
const billingCycle = ref<'monthly' | 'yearly'>('monthly')

const filteredPlans = computed(() => {
  return subscriptionsStore.plans.filter(plan => {
    if (billingCycle.value === 'monthly') {
      return plan.billing_cycle === 'monthly'
    }
    return plan.billing_cycle === 'yearly'
  })
})

async function loadPlans() {
  loading.value = true
  try {
    await subscriptionsStore.fetchPlans()
    await subscriptionsStore.fetchCurrentSubscription()
  } finally {
    loading.value = false
  }
}

async function subscribe(plan: SubscriptionPlan) {
  subscribing.value = plan.id
  try {
    await subscriptionsStore.subscribe(plan.id)
    router.push({ name: 'my-courses' })
  } catch (error) {
    console.error('Error subscribing:', error)
  } finally {
    subscribing.value = null
  }
}

function isCurrentPlan(plan: SubscriptionPlan): boolean {
  return subscriptionsStore.currentSubscription?.plan?.id === plan.id
}

function formatPrice(price: number, currency: string): string {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: currency || 'ARS',
  }).format(price)
}

function getBillingLabel(cycle: string): string {
  const labels: Record<string, string> = {
    weekly: '/semana',
    monthly: '/mes',
    quarterly: '/trimestre',
    biannual: '/semestre',
    yearly: '/año',
    lifetime: 'único',
  }
  return labels[cycle] || ''
}

onMounted(() => {
  loadPlans()
})
</script>

<template>
  <VContainer class="py-8">
    <!-- Header -->
    <div class="text-center mb-8">
      <h1 class="text-h3 font-weight-bold mb-4">
        {{ t('subscriptions.choose_plan') }}
      </h1>
      <p class="text-body-1 text-medium-emphasis mx-auto" style="max-width: 600px;">
        {{ t('subscriptions.choose_plan_description') }}
      </p>

      <!-- Billing Toggle -->
      <VBtnToggle
        v-model="billingCycle"
        mandatory
        class="mt-6"
        color="primary"
      >
        <VBtn value="monthly">
          Mensual
        </VBtn>
        <VBtn value="yearly">
          Anual
          <VChip size="x-small" color="success" class="ms-2">
            -20%
          </VChip>
        </VBtn>
      </VBtnToggle>
    </div>

    <!-- Loading -->
    <VRow v-if="loading" justify="center">
      <VCol v-for="n in 3" :key="n" cols="12" md="4">
        <VSkeletonLoader type="card" height="400" />
      </VCol>
    </VRow>

    <!-- Plans Grid -->
    <VRow v-else justify="center">
      <VCol
        v-for="plan in filteredPlans"
        :key="plan.id"
        cols="12"
        md="4"
      >
        <VCard
          class="plan-card h-100"
          :class="{ 'plan-featured': plan.is_featured }"
          :elevation="plan.is_featured ? 8 : 2"
        >
          <!-- Featured Badge -->
          <VChip
            v-if="plan.is_featured"
            color="primary"
            class="plan-badge"
          >
            Más popular
          </VChip>

          <VCardText class="text-center pa-6">
            <!-- Plan Name -->
            <h2 class="text-h5 font-weight-bold mb-2">
              {{ plan.name }}
            </h2>

            <!-- Description -->
            <p class="text-body-2 text-medium-emphasis mb-4">
              {{ plan.description }}
            </p>

            <!-- Price -->
            <div class="mb-6">
              <span class="text-h3 font-weight-bold">
                {{ formatPrice(plan.price, plan.currency) }}
              </span>
              <span class="text-body-2 text-medium-emphasis">
                {{ getBillingLabel(plan.billing_cycle) }}
              </span>
            </div>

            <!-- Trial Badge -->
            <VChip
              v-if="plan.trial_days > 0"
              color="success"
              variant="tonal"
              class="mb-4"
            >
              {{ plan.trial_days }} días de prueba gratis
            </VChip>

            <!-- CTA Button -->
            <VBtn
              v-if="isCurrentPlan(plan)"
              color="success"
              variant="tonal"
              block
              disabled
            >
              <VIcon icon="mdi-check" class="me-2" />
              Plan actual
            </VBtn>

            <VBtn
              v-else
              :color="plan.is_featured ? 'primary' : 'default'"
              :variant="plan.is_featured ? 'flat' : 'outlined'"
              block
              :loading="subscribing === plan.id"
              @click="subscribe(plan)"
            >
              {{ plan.trial_days > 0 ? 'Comenzar prueba gratis' : 'Suscribirse' }}
            </VBtn>
          </VCardText>

          <VDivider />

          <!-- Features -->
          <VCardText class="pa-6">
            <h4 class="text-subtitle-2 font-weight-medium mb-4">
              Incluye:
            </h4>
            <VList density="compact" class="pa-0">
              <VListItem
                v-for="(feature, index) in plan.features"
                :key="index"
                class="px-0"
              >
                <template #prepend>
                  <VIcon icon="mdi-check-circle" color="success" size="small" />
                </template>
                <VListItemTitle class="text-body-2">
                  {{ feature }}
                </VListItemTitle>
              </VListItem>

              <!-- Course Access -->
              <VListItem class="px-0">
                <template #prepend>
                  <VIcon icon="mdi-school" color="primary" size="small" />
                </template>
                <VListItemTitle class="text-body-2">
                  {{
                    plan.course_access === 'all'
                      ? 'Acceso a todos los cursos'
                      : plan.course_access === 'specific'
                        ? `Acceso a ${plan.course_ids?.length || 0} cursos`
                        : 'Acceso por categoría'
                  }}
                </VListItemTitle>
              </VListItem>
            </VList>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Current Subscription Info -->
    <VCard v-if="subscriptionsStore.currentSubscription" class="mt-8">
      <VCardTitle>Tu suscripción actual</VCardTitle>
      <VCardText>
        <VRow align="center">
          <VCol cols="12" md="6">
            <div class="d-flex align-center">
              <VAvatar color="primary" variant="tonal" size="48" class="me-4">
                <VIcon icon="mdi-crown" />
              </VAvatar>
              <div>
                <div class="text-subtitle-1 font-weight-medium">
                  {{ subscriptionsStore.currentSubscription.plan?.name }}
                </div>
                <div class="text-body-2 text-medium-emphasis">
                  <VChip
                    size="small"
                    :color="subscriptionsStore.currentSubscription.is_active ? 'success' : 'warning'"
                  >
                    {{ subscriptionsStore.currentSubscription.status }}
                  </VChip>
                </div>
              </div>
            </div>
          </VCol>
          <VCol cols="12" md="3">
            <div class="text-body-2 text-medium-emphasis">Próximo cobro</div>
            <div class="text-subtitle-1">
              {{ subscriptionsStore.currentSubscription.next_payment_at
                ? new Date(subscriptionsStore.currentSubscription.next_payment_at).toLocaleDateString('es-AR')
                : 'N/A'
              }}
            </div>
          </VCol>
          <VCol cols="12" md="3" class="text-md-end">
            <VBtn
              color="error"
              variant="text"
              :to="{ name: 'my-subscription' }"
            >
              Administrar suscripción
            </VBtn>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>

    <!-- FAQ Section -->
    <div class="mt-12">
      <h2 class="text-h5 font-weight-bold text-center mb-6">
        Preguntas frecuentes
      </h2>

      <VExpansionPanels>
        <VExpansionPanel title="¿Puedo cancelar en cualquier momento?">
          <VExpansionPanelText>
            Sí, puedes cancelar tu suscripción en cualquier momento. Seguirás teniendo acceso
            hasta el final de tu período de facturación actual.
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="¿Qué métodos de pago aceptan?">
          <VExpansionPanelText>
            Aceptamos tarjetas de crédito/débito, MercadoPago y transferencia bancaria.
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="¿Puedo cambiar de plan después?">
          <VExpansionPanelText>
            Sí, puedes actualizar o cambiar tu plan en cualquier momento desde la
            configuración de tu cuenta.
          </VExpansionPanelText>
        </VExpansionPanel>

        <VExpansionPanel title="¿Hay reembolsos?">
          <VExpansionPanelText>
            Ofrecemos una garantía de devolución de 7 días si no estás satisfecho
            con tu suscripción.
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </div>
  </VContainer>
</template>

<style scoped>
.plan-card {
  position: relative;
  transition: transform 0.2s;
}

.plan-card:hover {
  transform: translateY(-4px);
}

.plan-featured {
  border: 2px solid rgb(var(--v-theme-primary));
}

.plan-badge {
  position: absolute;
  top: -12px;
  left: 50%;
  transform: translateX(-50%);
}
</style>
