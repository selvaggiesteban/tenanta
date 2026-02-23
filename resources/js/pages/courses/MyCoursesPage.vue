<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useEnrollmentsStore } from '@/stores/enrollments'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const router = useRouter()
const enrollmentsStore = useEnrollmentsStore()

const activeTab = ref('in_progress')
const loading = ref(true)

const tabs = [
  { value: 'in_progress', title: 'En progreso', icon: 'mdi-play-circle' },
  { value: 'completed', title: 'Completados', icon: 'mdi-check-circle' },
  { value: 'all', title: 'Todos', icon: 'mdi-view-list' },
]

const filteredEnrollments = computed(() => {
  const enrollments = enrollmentsStore.enrollments

  switch (activeTab.value) {
    case 'in_progress':
      return enrollments.filter(e => e.status === 'active' && e.progress_percentage < 100)
    case 'completed':
      return enrollments.filter(e => e.status === 'completed')
    default:
      return enrollments
  }
})

async function loadEnrollments() {
  loading.value = true
  try {
    await enrollmentsStore.fetchEnrollments()
  } finally {
    loading.value = false
  }
}

function goToCourse(enrollmentId: number) {
  router.push({
    name: 'course-player',
    params: { enrollmentId }
  })
}

function getStatusColor(status: string): string {
  const colors: Record<string, string> = {
    active: 'primary',
    completed: 'success',
    expired: 'error',
  }
  return colors[status] || 'grey'
}

function getStatusText(status: string): string {
  const texts: Record<string, string> = {
    active: 'En progreso',
    completed: 'Completado',
    expired: 'Expirado',
  }
  return texts[status] || status
}

onMounted(() => {
  loadEnrollments()
})
</script>

<template>
  <VContainer fluid>
    <!-- Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <h1 class="text-h4 font-weight-bold">
          {{ t('courses.my_courses') }}
        </h1>
        <p class="text-body-1 text-medium-emphasis mt-2">
          {{ t('courses.my_courses_description') }}
        </p>
      </VCol>
    </VRow>

    <!-- Stats Cards -->
    <VRow class="mb-6">
      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar color="primary" variant="tonal" size="48" class="me-4">
              <VIcon icon="mdi-book-open-page-variant" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">
                {{ enrollmentsStore.enrollments.length }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                Total cursos
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar color="warning" variant="tonal" size="48" class="me-4">
              <VIcon icon="mdi-progress-clock" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">
                {{ enrollmentsStore.inProgressCourses.length }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                En progreso
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>

      <VCol cols="12" sm="4">
        <VCard>
          <VCardText class="d-flex align-center">
            <VAvatar color="success" variant="tonal" size="48" class="me-4">
              <VIcon icon="mdi-trophy" />
            </VAvatar>
            <div>
              <div class="text-h5 font-weight-bold">
                {{ enrollmentsStore.completedEnrollments.length }}
              </div>
              <div class="text-body-2 text-medium-emphasis">
                Completados
              </div>
            </div>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Tabs -->
    <VCard>
      <VTabs v-model="activeTab" grow>
        <VTab
          v-for="tab in tabs"
          :key="tab.value"
          :value="tab.value"
        >
          <VIcon :icon="tab.icon" class="me-2" />
          {{ tab.title }}
        </VTab>
      </VTabs>

      <VDivider />

      <VCardText>
        <!-- Loading -->
        <div v-if="loading" class="py-4">
          <VRow>
            <VCol v-for="n in 3" :key="n" cols="12" md="4">
              <VSkeletonLoader type="card" />
            </VCol>
          </VRow>
        </div>

        <!-- Enrollments Grid -->
        <VRow v-else-if="filteredEnrollments.length > 0">
          <VCol
            v-for="enrollment in filteredEnrollments"
            :key="enrollment.id"
            cols="12"
            md="6"
            lg="4"
          >
            <VCard
              class="enrollment-card h-100"
              @click="goToCourse(enrollment.id)"
            >
              <VImg
                :src="enrollment.course?.thumbnail || '/images/course-placeholder.jpg'"
                height="140"
                cover
              >
                <VChip
                  size="small"
                  :color="getStatusColor(enrollment.status)"
                  class="ma-2 position-absolute"
                  style="top: 0; right: 0;"
                >
                  {{ getStatusText(enrollment.status) }}
                </VChip>
              </VImg>

              <VCardTitle class="text-subtitle-1">
                {{ enrollment.course?.title }}
              </VCardTitle>

              <VCardText>
                <!-- Progress -->
                <div class="mb-3">
                  <div class="d-flex justify-space-between text-caption mb-1">
                    <span>Progreso</span>
                    <span class="font-weight-medium">{{ enrollment.progress_percentage }}%</span>
                  </div>
                  <VProgressLinear
                    :model-value="enrollment.progress_percentage"
                    :color="enrollment.status === 'completed' ? 'success' : 'primary'"
                    height="8"
                    rounded
                  />
                </div>

                <!-- Stats -->
                <div class="d-flex align-center gap-4 text-caption text-medium-emphasis">
                  <span>
                    <VIcon size="small" icon="mdi-check-circle" class="me-1" />
                    {{ enrollment.completed_topics }}/{{ enrollment.total_topics }} lecciones
                  </span>
                </div>

                <!-- Last Activity -->
                <div v-if="enrollment.last_activity_at" class="text-caption text-medium-emphasis mt-2">
                  Última actividad: {{ formatDate(enrollment.last_activity_at) }}
                </div>
              </VCardText>

              <VCardActions>
                <VBtn
                  :color="enrollment.status === 'completed' ? 'success' : 'primary'"
                  variant="tonal"
                  block
                >
                  <VIcon
                    :icon="enrollment.status === 'completed' ? 'mdi-refresh' : 'mdi-play'"
                    class="me-2"
                  />
                  {{ enrollment.status === 'completed' ? 'Revisar' : 'Continuar' }}
                </VBtn>
              </VCardActions>
            </VCard>
          </VCol>
        </VRow>

        <!-- Empty State -->
        <div v-else class="text-center py-8">
          <VIcon icon="mdi-school-outline" size="64" color="grey" />
          <h3 class="text-h6 mt-4">
            {{ activeTab === 'completed' ? 'Aún no has completado ningún curso' : 'No tienes cursos' }}
          </h3>
          <p class="text-body-2 text-medium-emphasis">
            {{ activeTab === 'completed'
              ? 'Completa un curso para verlo aquí'
              : 'Explora nuestro catálogo para inscribirte'
            }}
          </p>
          <VBtn
            color="primary"
            class="mt-4"
            :to="{ name: 'course-catalog' }"
          >
            Ver catálogo
          </VBtn>
        </div>
      </VCardText>
    </VCard>
  </VContainer>
</template>

<script lang="ts">
function formatDate(dateString: string): string {
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('es-AR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date)
}
</script>

<style scoped>
.enrollment-card {
  cursor: pointer;
  transition: transform 0.2s, box-shadow 0.2s;
}

.enrollment-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}
</style>
