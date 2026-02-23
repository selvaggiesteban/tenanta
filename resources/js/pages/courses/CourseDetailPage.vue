<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCoursesStore } from '@/stores/courses'
import { useEnrollmentsStore } from '@/stores/enrollments'
import { useCourseAccess } from '@/composables/useCourseAccess'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const coursesStore = useCoursesStore()
const enrollmentsStore = useEnrollmentsStore()
const courseAccess = useCourseAccess()

const loading = ref(true)
const enrolling = ref(false)
const expandedBlocks = ref<number[]>([])

const slug = computed(() => route.params.slug as string)

const course = computed(() => coursesStore.currentCourse)

const totalDuration = computed(() => {
  if (!course.value) return '0h 0m'
  const hours = Math.floor(course.value.total_duration_seconds / 3600)
  const minutes = Math.floor((course.value.total_duration_seconds % 3600) / 60)
  return `${hours}h ${minutes}m`
})

async function loadCourse() {
  loading.value = true
  try {
    await coursesStore.fetchCourseBySlug(slug.value)
    if (course.value) {
      await courseAccess.checkAccess(course.value.id)
      // Expand first block by default
      if (course.value.blocks?.length) {
        expandedBlocks.value = [course.value.blocks[0].id]
      }
    }
  } catch (error) {
    console.error('Error loading course:', error)
  } finally {
    loading.value = false
  }
}

async function handleEnroll() {
  if (!course.value) return

  if (courseAccess.needsSubscription()) {
    router.push({ name: 'subscription-plans' })
    return
  }

  enrolling.value = true
  try {
    const enrollment = await courseAccess.enrollInCourse(course.value.id)
    // Navigate to course player
    router.push({
      name: 'course-player',
      params: { enrollmentId: enrollment.id }
    })
  } catch (error) {
    console.error('Error enrolling:', error)
  } finally {
    enrolling.value = false
  }
}

function goToPlayer() {
  const enrollment = courseAccess.accessDetails.value?.enrollment
  if (enrollment) {
    router.push({
      name: 'course-player',
      params: { enrollmentId: enrollment.id }
    })
  }
}

function formatDuration(seconds: number): string {
  const mins = Math.floor(seconds / 60)
  return `${mins} min`
}

onMounted(() => {
  loadCourse()
})
</script>

<template>
  <div>
    <!-- Loading -->
    <VContainer v-if="loading" class="py-8">
      <VRow>
        <VCol cols="12" md="8">
          <VSkeletonLoader type="image" height="300" class="mb-4" />
          <VSkeletonLoader type="heading" class="mb-4" />
          <VSkeletonLoader type="paragraph" />
        </VCol>
        <VCol cols="12" md="4">
          <VSkeletonLoader type="card" />
        </VCol>
      </VRow>
    </VContainer>

    <!-- Course Content -->
    <div v-else-if="course">
      <!-- Hero Section -->
      <div class="course-hero bg-primary pa-6 pa-md-10">
        <VContainer>
          <VRow>
            <VCol cols="12" md="8">
              <VChip
                size="small"
                color="white"
                variant="outlined"
                class="mb-4"
              >
                {{ course.level_label }}
              </VChip>

              <h1 class="text-h3 text-md-h2 font-weight-bold text-white mb-4">
                {{ course.title }}
              </h1>

              <p class="text-body-1 text-white text-opacity-high mb-6">
                {{ course.short_description }}
              </p>

              <div class="d-flex flex-wrap gap-4 text-white">
                <span class="d-flex align-center">
                  <VIcon icon="mdi-play-circle" class="me-2" />
                  {{ course.total_topics }} lecciones
                </span>
                <span class="d-flex align-center">
                  <VIcon icon="mdi-clock-outline" class="me-2" />
                  {{ totalDuration }}
                </span>
                <span v-if="course.rating" class="d-flex align-center">
                  <VIcon icon="mdi-star" color="warning" class="me-2" />
                  {{ course.rating }} ({{ course.reviews_count }} reseñas)
                </span>
                <span class="d-flex align-center">
                  <VIcon icon="mdi-account-group" class="me-2" />
                  {{ course.enrolled_count }} estudiantes
                </span>
              </div>

              <!-- Instructor -->
              <div v-if="course.instructor" class="d-flex align-center mt-6">
                <VAvatar size="48" class="me-3">
                  <VImg
                    v-if="course.instructor.avatar"
                    :src="course.instructor.avatar"
                  />
                  <VIcon v-else icon="mdi-account" />
                </VAvatar>
                <div>
                  <div class="text-caption text-white text-opacity-medium">Instructor</div>
                  <div class="text-body-1 text-white font-weight-medium">
                    {{ course.instructor.name }}
                  </div>
                </div>
              </div>
            </VCol>
          </VRow>
        </VContainer>
      </div>

      <VContainer class="mt-n10">
        <VRow>
          <!-- Main Content -->
          <VCol cols="12" md="8">
            <!-- About -->
            <VCard class="mb-6">
              <VCardTitle>Acerca de este curso</VCardTitle>
              <VCardText>
                <div v-html="course.description" class="course-description" />
              </VCardText>
            </VCard>

            <!-- What you'll learn -->
            <VCard v-if="course.what_you_learn?.length" class="mb-6">
              <VCardTitle>Lo que aprenderás</VCardTitle>
              <VCardText>
                <VRow>
                  <VCol
                    v-for="(item, index) in course.what_you_learn"
                    :key="index"
                    cols="12"
                    sm="6"
                  >
                    <div class="d-flex align-start">
                      <VIcon icon="mdi-check-circle" color="success" class="me-3 mt-1" />
                      <span>{{ item }}</span>
                    </div>
                  </VCol>
                </VRow>
              </VCardText>
            </VCard>

            <!-- Course Content -->
            <VCard class="mb-6">
              <VCardTitle class="d-flex justify-space-between align-center">
                <span>Contenido del curso</span>
                <span class="text-body-2 text-medium-emphasis">
                  {{ course.total_blocks }} módulos • {{ course.total_topics }} lecciones
                </span>
              </VCardTitle>
              <VCardText class="pa-0">
                <VExpansionPanels v-model="expandedBlocks" multiple>
                  <VExpansionPanel
                    v-for="block in course.blocks"
                    :key="block.id"
                    :value="block.id"
                  >
                    <VExpansionPanelTitle>
                      <div class="d-flex align-center justify-space-between w-100 pe-4">
                        <span class="font-weight-medium">{{ block.title }}</span>
                        <span class="text-caption text-medium-emphasis">
                          {{ block.topics_count }} lecciones
                        </span>
                      </div>
                    </VExpansionPanelTitle>
                    <VExpansionPanelText>
                      <VList density="compact" class="py-0">
                        <VListItem
                          v-for="topic in block.topics"
                          :key="topic.id"
                        >
                          <template #prepend>
                            <VIcon
                              :icon="getTopicIcon(topic.content_type)"
                              size="small"
                              class="me-2"
                            />
                          </template>
                          <VListItemTitle class="text-body-2">
                            {{ topic.title }}
                          </VListItemTitle>
                          <template #append>
                            <div class="d-flex align-center gap-2">
                              <VChip
                                v-if="topic.is_free_preview"
                                size="x-small"
                                color="success"
                              >
                                Preview
                              </VChip>
                              <span class="text-caption text-medium-emphasis">
                                {{ formatDuration(topic.video_duration_seconds) }}
                              </span>
                            </div>
                          </template>
                        </VListItem>
                      </VList>
                    </VExpansionPanelText>
                  </VExpansionPanel>
                </VExpansionPanels>
              </VCardText>
            </VCard>

            <!-- Requirements -->
            <VCard v-if="course.requirements?.length" class="mb-6">
              <VCardTitle>Requisitos</VCardTitle>
              <VCardText>
                <ul class="pl-4">
                  <li v-for="(req, index) in course.requirements" :key="index" class="mb-2">
                    {{ req }}
                  </li>
                </ul>
              </VCardText>
            </VCard>

            <!-- Target Audience -->
            <VCard v-if="course.target_audience?.length">
              <VCardTitle>¿Para quién es este curso?</VCardTitle>
              <VCardText>
                <ul class="pl-4">
                  <li v-for="(item, index) in course.target_audience" :key="index" class="mb-2">
                    {{ item }}
                  </li>
                </ul>
              </VCardText>
            </VCard>
          </VCol>

          <!-- Sidebar -->
          <VCol cols="12" md="4">
            <VCard class="sticky-card">
              <!-- Trailer/Thumbnail -->
              <VImg
                :src="course.thumbnail || '/images/course-placeholder.jpg'"
                height="200"
                cover
              >
                <div
                  v-if="course.trailer_video_url"
                  class="d-flex align-center justify-center fill-height"
                  style="background: rgba(0,0,0,0.3)"
                >
                  <VBtn icon size="x-large" color="white">
                    <VIcon icon="mdi-play" size="32" />
                  </VBtn>
                </div>
              </VImg>

              <VCardText class="pa-6">
                <!-- Price -->
                <div class="text-center mb-4">
                  <div class="text-h4 font-weight-bold">
                    {{ course.price === 0 ? 'Gratis' : course.formatted_price }}
                  </div>
                </div>

                <!-- Access Status -->
                <VAlert
                  v-if="courseAccess.hasEnrollment.value"
                  type="success"
                  variant="tonal"
                  class="mb-4"
                >
                  <div class="d-flex align-center justify-space-between">
                    <span>Progreso: {{ courseAccess.enrollmentProgress.value }}%</span>
                  </div>
                  <VProgressLinear
                    :model-value="courseAccess.enrollmentProgress.value"
                    color="success"
                    class="mt-2"
                  />
                </VAlert>

                <!-- CTA Button -->
                <VBtn
                  v-if="courseAccess.hasEnrollment.value"
                  color="primary"
                  size="large"
                  block
                  @click="goToPlayer"
                >
                  <VIcon icon="mdi-play" class="me-2" />
                  Continuar curso
                </VBtn>

                <VBtn
                  v-else
                  color="primary"
                  size="large"
                  block
                  :loading="enrolling"
                  @click="handleEnroll"
                >
                  {{ courseAccess.getEnrollButtonText() }}
                </VBtn>

                <!-- Course Features -->
                <VList density="compact" class="mt-4">
                  <VListItem>
                    <template #prepend>
                      <VIcon icon="mdi-play-circle-outline" />
                    </template>
                    <VListItemTitle>{{ course.total_topics }} lecciones</VListItemTitle>
                  </VListItem>
                  <VListItem>
                    <template #prepend>
                      <VIcon icon="mdi-clock-outline" />
                    </template>
                    <VListItemTitle>{{ totalDuration }} de contenido</VListItemTitle>
                  </VListItem>
                  <VListItem>
                    <template #prepend>
                      <VIcon icon="mdi-cellphone" />
                    </template>
                    <VListItemTitle>Acceso en móvil y TV</VListItemTitle>
                  </VListItem>
                  <VListItem>
                    <template #prepend>
                      <VIcon icon="mdi-infinity" />
                    </template>
                    <VListItemTitle>Acceso de por vida</VListItemTitle>
                  </VListItem>
                  <VListItem>
                    <template #prepend>
                      <VIcon icon="mdi-certificate-outline" />
                    </template>
                    <VListItemTitle>Certificado de finalización</VListItemTitle>
                  </VListItem>
                </VList>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VContainer>
    </div>

    <!-- Not Found -->
    <VContainer v-else class="py-16 text-center">
      <VIcon icon="mdi-alert-circle-outline" size="64" color="grey" />
      <h2 class="text-h5 mt-4">Curso no encontrado</h2>
      <p class="text-body-2 text-medium-emphasis">
        El curso que buscas no existe o ha sido eliminado.
      </p>
      <VBtn
        color="primary"
        class="mt-4"
        :to="{ name: 'course-catalog' }"
      >
        Ver catálogo
      </VBtn>
    </VContainer>
  </div>
</template>

<script lang="ts">
function getTopicIcon(contentType: string): string {
  const icons: Record<string, string> = {
    video: 'mdi-play-circle-outline',
    text: 'mdi-file-document-outline',
    pdf: 'mdi-file-pdf-box',
    quiz: 'mdi-help-circle-outline',
  }
  return icons[contentType] || 'mdi-file-outline'
}
</script>

<style scoped>
.course-hero {
  position: relative;
}

.sticky-card {
  position: sticky;
  top: 80px;
}

.course-description {
  line-height: 1.7;
}

.course-description :deep(p) {
  margin-bottom: 1rem;
}

.course-description :deep(ul),
.course-description :deep(ol) {
  padding-left: 1.5rem;
  margin-bottom: 1rem;
}
</style>
