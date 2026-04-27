<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const features = [
  {
    title: 'Posicionamiento Web',
    description: 'Estrategias SEO probadas para aparecer primero cuando tus clientes te buscan.',
    icon: 'mdi-magnify-scan'
  },
  {
    title: 'Landing Pages',
    description: 'Sitios web optimizados para convertir visitantes en clientes reales.',
    icon: 'mdi-rocket-launch-outline'
  },
  {
    title: 'Gestión Financiera',
    description: 'Inteligencia de negocio con dashboards como Dizteku y Piblo.',
    icon: 'mdi-chart-line'
  }
]

const reviews = [
  {
    id: 1,
    author: 'Martín G.',
    role: 'Dueño de Pyme',
    text: 'Desde que implementamos Tenanta, nuestras ventas se duplicaron. El equipo de soporte es increíble.',
    rating: 5,
    date: 'Hace 2 meses'
  },
  {
    id: 2,
    author: 'Lucía M.',
    role: 'Directora de Agencia',
    text: 'La auditoría SEO automática nos ahorra horas de trabajo. Una plataforma 10 de 10.',
    rating: 5,
    date: 'Hace 1 mes'
  },
  {
    id: 3,
    author: 'Carlos R.',
    role: 'Emprendedor',
    text: 'Fácil de usar y el funnel de WhatsApp nos trajo muchísimos leads cualificados.',
    rating: 5,
    date: 'Hace 3 semanas'
  }
]

const handleHire = () => router.push({ name: 'pricing' })
const handleLearnMore = () => {
  document.getElementById('features-section')?.scrollIntoView({ behavior: 'smooth' })
}
</script>

<template>
  <div class="homepage bg-white font-sans text-gray-900">
    
    <!-- HERO SECTION (Blueprint: selvaggiesteban.dev) -->
    <section class="hero relative pt-20 pb-32 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto flex flex-col items-center text-center">
      <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-50 via-white to-white opacity-70"></div>
      
      <span class="text-indigo-600 font-bold tracking-wider uppercase text-sm mb-4 block">
        {{ $t('public.home.hero.badge') }}
      </span>
      
      <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-gray-900 mb-6 max-w-4xl mx-auto leading-tight">
        {{ $t('public.home.hero.title') }} <span class="text-indigo-600">{{ $t('public.home.hero.title_highlight') }}</span>.
      </h1>
      
      <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto mb-10 leading-relaxed">
        {{ $t('public.home.hero.subtitle') }}
      </p>
      
      <div class="flex flex-col sm:flex-row gap-4 justify-center w-full max-w-md mx-auto">
        <button @click="handleHire" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-lg">
          {{ $t('public.home.hero.primary_btn') }}
        </button>
        <button @click="handleLearnMore" class="w-full sm:w-auto px-8 py-4 bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-bold rounded-xl transition-all duration-300 text-lg">
          {{ $t('public.home.hero.secondary_btn') }}
        </button>
      </div>

      <!-- SOCIAL PROOF WIDGET (Trustindex Style) -->
      <div class="mt-20 pt-10 border-t border-gray-100 w-full max-w-5xl">
        <div class="flex flex-col items-center justify-center mb-8">
          <div class="flex space-x-1 text-yellow-400 mb-2">
            <VIcon icon="mdi-star" size="24" v-for="i in 5" :key="i" />
          </div>
          <p class="text-gray-700 font-medium">Excelente <span class="font-bold text-gray-900">4.9 de 5</span> basado en más de <span class="underline">150 reseñas</span></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left">
          <div v-for="review in reviews" :key="review.id" class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center mb-4">
              <div class="flex space-x-1 text-yellow-400 text-sm">
                <VIcon icon="mdi-star" size="16" v-for="i in 5" :key="i" />
              </div>
              <span class="ml-auto text-xs text-gray-400 flex items-center">
                <VIcon icon="mdi-check-decagram" color="blue" size="14" class="mr-1" />
                Verificado
              </span>
            </div>
            <p class="text-gray-700 text-sm italic mb-4 leading-relaxed">"{{ review.text }}"</p>
            <div class="flex items-center">
              <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-lg mr-3">
                {{ review.author.charAt(0) }}
              </div>
              <div>
                <p class="font-bold text-sm text-gray-900">{{ review.author }}</p>
                <p class="text-xs text-gray-500">{{ review.role }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- FEATURES SECTION (Blueprint: Services) -->
    <section id="features-section" class="py-24 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-16">Todo lo que necesitas para crecer</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
          <div v-for="feature in features" :key="feature.title" class="flex flex-col items-center">
            <div class="w-20 h-20 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mb-6 shadow-sm">
              <VIcon :icon="feature.icon" size="40" />
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">{{ feature.title }}</h3>
            <p class="text-gray-600 leading-relaxed">{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ SECTION (Blueprint: Acordeón) -->
    <section class="py-24 bg-white">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-extrabold text-center text-gray-900 mb-12">Preguntas Frecuentes</h2>
        
        <div class="space-y-4">
          <!-- Item 1 -->
          <details class="group border border-gray-200 rounded-lg bg-gray-50 open:bg-white open:shadow-md transition-all duration-200">
            <summary class="flex justify-between items-center font-medium cursor-pointer list-none p-6 text-gray-900">
              <span>¿Qué incluye el diseño de la Landing Page SEO?</span>
              <span class="transition group-open:rotate-180">
                <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
              </span>
            </summary>
            <p class="text-gray-600 mt-3 group-open:animate-fadeIn px-6 pb-6 leading-relaxed">
              Incluye un diseño optimizado para conversión, redacción SEO (copywriting), integración con Google Business Profile, panel de control de analíticas (CMO) y soporte técnico especializado.
            </p>
          </details>
          <!-- Item 2 -->
          <details class="group border border-gray-200 rounded-lg bg-gray-50 open:bg-white open:shadow-md transition-all duration-200">
            <summary class="flex justify-between items-center font-medium cursor-pointer list-none p-6 text-gray-900">
              <span>¿Cómo funciona el módulo de WhatsApp?</span>
              <span class="transition group-open:rotate-180">
                <svg fill="none" height="24" shape-rendering="geometricPrecision" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" viewBox="0 0 24 24" width="24"><path d="M6 9l6 6 6-6"></path></svg>
              </span>
            </summary>
            <p class="text-gray-600 mt-3 group-open:animate-fadeIn px-6 pb-6 leading-relaxed">
              Generamos links directos de 'wa.me' con mensajes pre-diseñados (funnels de 5 pasos) para que contactes a tus prospectos de manera automatizada y con un solo clic desde tu CRM.
            </p>
          </details>
        </div>
      </div>
    </section>

    <!-- BOTTOM CTA -->
    <section class="py-20 bg-indigo-600">
      <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-extrabold text-white mb-6">¿Listo para dominar tu mercado?</h2>
        <p class="text-xl text-indigo-100 mb-10">Únete a decenas de negocios que ya escalaron sus ventas con Tenanta.</p>
        <button @click="handleHire" class="px-10 py-5 bg-white hover:bg-gray-50 text-indigo-600 font-bold rounded-xl shadow-xl transition-all duration-300 transform hover:scale-105 text-lg">
          Comenzar Ahora
        </button>
      </div>
    </section>
  </div>
</template>

<style scoped>
.group-open\:animate-fadeIn {
  animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
  0% { opacity: 0; transform: translateY(-10px); }
  100% { opacity: 1; transform: translateY(0); }
}
</style>
