<script setup lang="ts">
import { ref, computed } from 'vue'

const isYearly = ref(false)

const plans = [
  {
    name: 'Inicial',
    id: 'inicial',
    objective: 'Establece tu presencia básica',
    price_monthly: 57000,
    price_yearly: 47500, // Simulando descuento
    features: [
      'Landing Page SEO automática',
      'CRM Financiero Básico (Dizteku)',
      'Gestión de Tareas',
      'Soporte por Tickets',
    ],
    ideal_for: 'Emprendedores y profesionales independientes.',
    button_text: 'Contratar Inicial',
    popular: false
  },
  {
    name: 'Crecimiento',
    id: 'crecimiento',
    objective: 'Escala tus ventas y comunicación',
    price_monthly: 100000,
    price_yearly: 83300,
    features: [
      'Todo en Inicial',
      'Módulo de WhatsApp Connect',
      'Análisis SEO Avanzado',
      'Integración con WordPress',
      'Funnels de 5 pasos automáticos',
    ],
    ideal_for: 'Pymes y negocios en expansión.',
    button_text: 'Contratar Crecimiento',
    popular: true
  },
  {
    name: 'Dominación',
    id: 'dominacion',
    objective: 'Control total y máxima autoridad',
    price_monthly: 200000,
    price_yearly: 166600,
    features: [
      'Todo en Crecimiento',
      'Dashboard Financiero Full (Piblo/CMO)',
      'Onboarding Masivo Garantizado',
      'Auditoría SEO Mensual Proactiva',
      'Prioridad en Soporte 24/7',
    ],
    ideal_for: 'Empresas consolidadas y agencias.',
    button_text: 'Contratar Dominación',
    popular: false
  }
]

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('es-AR', {
    style: 'currency',
    currency: 'ARS',
    maximumFractionDigits: 0
  }).format(price)
}

const handleHire = (planId: string) => {
  // Lógica Academicus: Agregar al carrito y redirigir
  console.log(`Contratando plan: ${planId} - Facturación: ${isYearly.value ? 'Anual' : 'Mensual'}`)
}
</script>

<template>
  <div class="pricing-section py-16 px-4 bg-gray-50">
    <div class="max-w-7xl mx-auto text-center">
      <h2 class="text-4xl font-bold text-gray-900 mb-4">Planes diseñados para tu crecimiento</h2>
      <p class="text-xl text-gray-600 mb-12">Selecciona el nivel de ventaja competitiva que tu negocio necesita hoy.</p>

      <!-- Selector Deslizador -->
      <div class="flex items-center justify-center mb-16 space-x-4">
        <span :class="{'text-gray-900 font-semibold': !isYearly, 'text-gray-500': isYearly}">Mensual</span>
        <button 
          @click="isYearly = !isYearly"
          class="relative w-16 h-8 rounded-full transition-colors duration-300 focus:outline-none"
          :class="isYearly ? 'bg-indigo-600' : 'bg-gray-300'"
        >
          <div 
            class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full transition-transform duration-300 transform"
            :class="isYearly ? 'translate-x-8' : 'translate-x-0'"
          ></div>
        </button>
        <span :class="{'text-gray-900 font-semibold': isYearly, 'text-gray-500': !isYearly}">Anual <span class="text-green-600 text-sm font-bold">(Ahorra 20%)</span></span>
      </div>

      <!-- Tablas de Precios -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div 
          v-for="plan in plans" 
          :key="plan.id"
          class="pricing-card bg-white rounded-2xl shadow-xl overflow-hidden border-2 transition-transform hover:scale-105"
          :class="plan.popular ? 'border-indigo-500 relative' : 'border-transparent'"
        >
          <div v-if="plan.popular" class="bg-indigo-500 text-white text-xs font-bold uppercase py-1 px-4 absolute top-4 right-0 transform translate-x-4 rotate-45 w-32 text-center">
            Popular
          </div>
          
          <div class="p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
            <p class="text-sm text-indigo-600 font-semibold mb-6 uppercase tracking-wider">{{ plan.objective }}</p>
            
            <div class="mb-8">
              <span class="text-5xl font-bold text-gray-900">{{ formatPrice(isYearly ? plan.price_yearly : plan.price_monthly) }}</span>
              <span class="text-gray-500">/mes</span>
            </div>

            <ul class="text-left space-y-4 mb-8">
              <li v-for="feature in plan.features" :key="feature" class="flex items-start">
                <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-gray-600">{{ feature }}</span>
              </li>
            </ul>

            <div class="bg-gray-50 p-4 rounded-lg mb-8">
              <p class="text-sm text-gray-500 italic"><strong>Ideal para:</strong> {{ plan.ideal_for }}</p>
            </div>

            <button 
              @click="handleHire(plan.id)"
              class="w-full py-4 px-6 rounded-xl font-bold text-lg transition-all duration-300"
              :class="plan.popular ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg' : 'bg-gray-900 text-white hover:bg-black'"
            >
              {{ plan.button_text }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.pricing-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
