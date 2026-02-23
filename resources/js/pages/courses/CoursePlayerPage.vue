<script setup lang="ts">
import { ref, onMounted, computed, watch, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEnrollmentsStore } from '@/stores/enrollments'
import { useCoursePlayer } from '@/composables/useCoursePlayer'
import type { Course, CourseTopic, CourseBlock } from '@/types/courses'

const route = useRoute()
const router = useRouter()
const enrollmentsStore = useEnrollmentsStore()

const loading = ref(true)
const sidebarOpen = ref(true)
const course = ref<Course | null>(null)
const enrollment = ref<any>(null)
const progress = ref<any>(null)

const enrollmentId = computed(() => Number(route.params.enrollmentId))

// Player composable will be initialized after enrollment loads
let player: ReturnType<typeof useCoursePlayer> | null = null

// Current topic from player or first topic
const currentTopic = computed(() => player?.currentTopic.value)
const currentBlock = computed(() => {
  if (!currentTopic.value || !course.value?.blocks) return null
  return course.value.blocks.find(b =>
    b.topics?.some(t => t.id === currentTopic.value?.id)
  )
})

async function loadContent() {
  loading.value = true
  try {
    const response = await enrollmentsStore.fetchCourseContent(enrollmentId.value)
    course.value = response.course
    enrollment.value = response.enrollment

    // Initialize player
    player = useCoursePlayer(enrollment.value)

    // Load progress
    const progressResponse = await enrollmentsStore.fetchEnrollment(enrollmentId.value)
    progress.value = progressResponse.progress

    // Select first incomplete topic or first topic
    selectInitialTopic()
  } catch (error) {
    console.error('Error loading course content:', error)
    router.push({ name: 'my-courses' })
  } finally {
    loading.value = false
  }
}

function selectInitialTopic() {
  if (!course.value?.blocks || !player) return

  // Find first incomplete topic
  for (const block of course.value.blocks) {
    for (const topic of block.topics || []) {
      if (!topic.progress?.is_completed) {
        selectTopic(topic)
        return
      }
    }
  }

  // If all completed, select first topic
  const firstTopic = course.value.blocks[0]?.topics?.[0]
  if (firstTopic) {
    selectTopic(firstTopic)
  }
}

function selectTopic(topic: CourseTopic) {
  if (player) {
    player.setTopic(topic)
  }
}

function getTopicIcon(topic: CourseTopic): string {
  const icons: Record<string, string> = {
    video: 'mdi-play-circle',
    text: 'mdi-file-document',
    pdf: 'mdi-file-pdf-box',
    quiz: 'mdi-help-circle',
  }
  return icons[topic.content_type] || 'mdi-file'
}

function isTopicCompleted(topic: CourseTopic): boolean {
  return topic.progress?.is_completed ?? false
}

function isTopicCurrent(topic: CourseTopic): boolean {
  return currentTopic.value?.id === topic.id
}

function formatDuration(seconds: number): string {
  const mins = Math.floor(seconds / 60)
  const secs = seconds % 60
  return `${mins}:${secs.toString().padStart(2, '0')}`
}

function goToNextTopic() {
  if (!course.value?.blocks || !currentTopic.value) return

  let foundCurrent = false
  for (const block of course.value.blocks) {
    for (const topic of block.topics || []) {
      if (foundCurrent) {
        selectTopic(topic)
        return
      }
      if (topic.id === currentTopic.value.id) {
        foundCurrent = true
      }
    }
  }
}

function goToPrevTopic() {
  if (!course.value?.blocks || !currentTopic.value) return

  let prevTopic: CourseTopic | null = null
  for (const block of course.value.blocks) {
    for (const topic of block.topics || []) {
      if (topic.id === currentTopic.value.id && prevTopic) {
        selectTopic(prevTopic)
        return
      }
      prevTopic = topic
    }
  }
}

// Keyboard shortcuts
function handleKeydown(event: KeyboardEvent) {
  if (player) {
    player.handleKeydown(event)
  }
}

onMounted(() => {
  loadContent()
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div class="course-player-layout">
    <!-- Loading -->
    <div v-if="loading" class="d-flex align-center justify-center fill-height">
      <VProgressCircular indeterminate color="primary" size="64" />
    </div>

    <template v-else-if="course && player">
      <!-- Sidebar Toggle (Mobile) -->
      <VBtn
        icon
        class="sidebar-toggle d-md-none"
        :class="{ 'sidebar-open': sidebarOpen }"
        @click="sidebarOpen = !sidebarOpen"
      >
        <VIcon :icon="sidebarOpen ? 'mdi-close' : 'mdi-menu'" />
      </VBtn>

      <!-- Sidebar -->
      <aside
        class="course-sidebar"
        :class="{ open: sidebarOpen }"
      >
        <!-- Course Info -->
        <div class="sidebar-header pa-4">
          <RouterLink :to="{ name: 'course-detail', params: { slug: course.slug } }" class="text-decoration-none">
            <h2 class="text-subtitle-1 font-weight-bold text-primary">
              {{ course.title }}
            </h2>
          </RouterLink>

          <!-- Progress -->
          <div class="mt-3">
            <div class="d-flex justify-space-between text-caption mb-1">
              <span>Progreso del curso</span>
              <span class="font-weight-medium">{{ enrollment.progress_percentage }}%</span>
            </div>
            <VProgressLinear
              :model-value="enrollment.progress_percentage"
              color="primary"
              height="6"
              rounded
            />
          </div>
        </div>

        <VDivider />

        <!-- Course Content -->
        <div class="sidebar-content">
          <VList density="compact" class="py-0">
            <template v-for="block in course.blocks" :key="block.id">
              <!-- Block Header -->
              <VListSubheader class="text-uppercase font-weight-bold px-4 py-2 bg-grey-lighten-4">
                {{ block.title }}
              </VListSubheader>

              <!-- Topics -->
              <VListItem
                v-for="topic in block.topics"
                :key="topic.id"
                :active="isTopicCurrent(topic)"
                :class="{ 'topic-completed': isTopicCompleted(topic) }"
                @click="selectTopic(topic); sidebarOpen = false"
              >
                <template #prepend>
                  <VIcon
                    v-if="isTopicCompleted(topic)"
                    icon="mdi-check-circle"
                    color="success"
                    size="small"
                  />
                  <VIcon
                    v-else
                    :icon="getTopicIcon(topic)"
                    size="small"
                    :color="isTopicCurrent(topic) ? 'primary' : undefined"
                  />
                </template>

                <VListItemTitle class="text-body-2">
                  {{ topic.title }}
                </VListItemTitle>

                <template #append>
                  <span class="text-caption text-medium-emphasis">
                    {{ formatDuration(topic.video_duration_seconds) }}
                  </span>
                </template>
              </VListItem>
            </template>
          </VList>
        </div>
      </aside>

      <!-- Main Content -->
      <main class="course-main" :class="{ 'sidebar-collapsed': !sidebarOpen }">
        <!-- Video/Content Player -->
        <div class="player-container">
          <template v-if="currentTopic">
            <!-- Video Player -->
            <div v-if="currentTopic.content_type === 'video'" class="video-wrapper">
              <iframe
                v-if="currentTopic.embed_url"
                :src="currentTopic.embed_url"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                class="video-iframe"
              />
              <div v-else class="video-placeholder d-flex align-center justify-center">
                <div class="text-center">
                  <VIcon icon="mdi-video-off" size="64" color="grey" />
                  <p class="mt-2 text-medium-emphasis">Video no disponible</p>
                </div>
              </div>
            </div>

            <!-- Text Content -->
            <div v-else-if="currentTopic.content_type === 'text'" class="text-content pa-6">
              <div v-html="currentTopic.content" class="prose" />
            </div>

            <!-- PDF Content -->
            <div v-else-if="currentTopic.content_type === 'pdf'" class="pdf-content">
              <iframe
                v-if="currentTopic.video_url"
                :src="currentTopic.video_url"
                class="pdf-iframe"
              />
            </div>
          </template>
        </div>

        <!-- Controls Bar -->
        <div class="controls-bar pa-4">
          <VRow align="center" no-gutters>
            <VCol cols="auto">
              <VBtn
                variant="text"
                :disabled="!currentTopic"
                @click="goToPrevTopic"
              >
                <VIcon icon="mdi-chevron-left" class="me-1" />
                Anterior
              </VBtn>
            </VCol>

            <VCol class="text-center">
              <VBtn
                v-if="currentTopic && !isTopicCompleted(currentTopic)"
                color="success"
                variant="tonal"
                @click="player?.markTopicComplete()"
              >
                <VIcon icon="mdi-check" class="me-2" />
                Marcar como completado
              </VBtn>
              <VChip v-else-if="currentTopic" color="success" variant="flat">
                <VIcon icon="mdi-check-circle" class="me-1" />
                Completado
              </VChip>
            </VCol>

            <VCol cols="auto">
              <VBtn
                variant="text"
                :disabled="!currentTopic"
                @click="goToNextTopic"
              >
                Siguiente
                <VIcon icon="mdi-chevron-right" class="ms-1" />
              </VBtn>
            </VCol>
          </VRow>
        </div>

        <!-- Topic Info -->
        <div v-if="currentTopic" class="topic-info pa-6">
          <h1 class="text-h5 font-weight-bold mb-2">
            {{ currentTopic.title }}
          </h1>

          <div class="d-flex align-center gap-4 text-body-2 text-medium-emphasis mb-4">
            <span v-if="currentBlock">
              <VIcon icon="mdi-folder" size="small" class="me-1" />
              {{ currentBlock.title }}
            </span>
            <span>
              <VIcon icon="mdi-clock-outline" size="small" class="me-1" />
              {{ formatDuration(currentTopic.video_duration_seconds) }}
            </span>
          </div>

          <p v-if="currentTopic.description" class="text-body-1">
            {{ currentTopic.description }}
          </p>

          <!-- Attachments -->
          <div v-if="currentTopic.attachments?.length" class="mt-6">
            <h3 class="text-subtitle-1 font-weight-medium mb-3">
              Recursos descargables
            </h3>
            <VList density="compact" class="bg-grey-lighten-4 rounded">
              <VListItem
                v-for="(attachment, index) in currentTopic.attachments"
                :key="index"
                :href="attachment.url"
                target="_blank"
              >
                <template #prepend>
                  <VIcon icon="mdi-file-download" />
                </template>
                <VListItemTitle>{{ attachment.name }}</VListItemTitle>
                <template #append>
                  <VIcon icon="mdi-open-in-new" size="small" />
                </template>
              </VListItem>
            </VList>
          </div>
        </div>
      </main>
    </template>
  </div>
</template>

<style scoped>
.course-player-layout {
  display: flex;
  height: 100vh;
  overflow: hidden;
  background: #f5f5f5;
}

.sidebar-toggle {
  position: fixed;
  top: 70px;
  left: 10px;
  z-index: 100;
}

.sidebar-toggle.sidebar-open {
  left: 310px;
}

.course-sidebar {
  width: 320px;
  min-width: 320px;
  background: white;
  border-right: 1px solid rgba(0, 0, 0, 0.12);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.sidebar-header {
  flex-shrink: 0;
}

.sidebar-content {
  flex: 1;
  overflow-y: auto;
}

.course-main {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.player-container {
  flex: 1;
  background: #000;
  position: relative;
  min-height: 0;
}

.video-wrapper {
  position: absolute;
  inset: 0;
}

.video-iframe {
  width: 100%;
  height: 100%;
}

.video-placeholder {
  width: 100%;
  height: 100%;
  background: #1a1a1a;
  color: white;
}

.text-content {
  background: white;
  height: 100%;
  overflow-y: auto;
}

.pdf-content {
  height: 100%;
}

.pdf-iframe {
  width: 100%;
  height: 100%;
  border: none;
}

.controls-bar {
  background: white;
  border-top: 1px solid rgba(0, 0, 0, 0.12);
  flex-shrink: 0;
}

.topic-info {
  background: white;
  overflow-y: auto;
  flex-shrink: 0;
  max-height: 40%;
}

.topic-completed {
  opacity: 0.7;
}

.prose {
  line-height: 1.7;
  max-width: 800px;
}

.prose :deep(h2) {
  margin-top: 2rem;
  margin-bottom: 1rem;
}

.prose :deep(p) {
  margin-bottom: 1rem;
}

.prose :deep(ul),
.prose :deep(ol) {
  padding-left: 1.5rem;
  margin-bottom: 1rem;
}

@media (max-width: 960px) {
  .course-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    z-index: 99;
    transform: translateX(-100%);
  }

  .course-sidebar.open {
    transform: translateX(0);
  }

  .course-main.sidebar-collapsed {
    margin-left: 0;
  }
}
</style>
