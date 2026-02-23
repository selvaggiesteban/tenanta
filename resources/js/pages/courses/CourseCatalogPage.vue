<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useCoursesStore } from '@/stores/courses'
import { useI18n } from 'vue-i18n'
import type { CourseFilters } from '@/types/courses'

const { t } = useI18n()
const coursesStore = useCoursesStore()

const search = ref('')
const selectedLevel = ref<string | null>(null)
const priceFilter = ref<string | null>(null)
const sortBy = ref('published_at')

const levels = [
  { value: 'beginner', title: 'Principiante' },
  { value: 'intermediate', title: 'Intermedio' },
  { value: 'advanced', title: 'Avanzado' },
]

const priceOptions = [
  { value: null, title: 'Todos' },
  { value: 'free', title: 'Gratis' },
  { value: 'paid', title: 'De pago' },
]

const sortOptions = [
  { value: 'published_at', title: 'Más recientes' },
  { value: 'popular', title: 'Más populares' },
  { value: 'rating', title: 'Mejor valorados' },
  { value: 'price', title: 'Precio' },
]

async function loadCourses() {
  const filters: CourseFilters = {
    search: search.value || undefined,
    level: selectedLevel.value || undefined,
    free: priceFilter.value === 'free' ? true : undefined,
    sort_by: sortBy.value as any,
  }

  await coursesStore.fetchCatalog(filters)
}

function handlePageChange(page: number) {
  coursesStore.fetchCatalog({ page })
}

// Watch filters
watch([search, selectedLevel, priceFilter, sortBy], () => {
  loadCourses()
}, { debounce: 300 })

onMounted(() => {
  loadCourses()
})
</script>

<template>
  <VContainer fluid>
    <!-- Header -->
    <VRow class="mb-6">
      <VCol cols="12">
        <h1 class="text-h4 font-weight-bold">
          {{ t('courses.catalog') }}
        </h1>
        <p class="text-body-1 text-medium-emphasis mt-2">
          {{ t('courses.catalog_description') }}
        </p>
      </VCol>
    </VRow>

    <!-- Filters -->
    <VRow class="mb-4">
      <VCol cols="12" md="4">
        <VTextField
          v-model="search"
          :label="t('common.search')"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="compact"
          clearable
          hide-details
        />
      </VCol>

      <VCol cols="6" md="2">
        <VSelect
          v-model="selectedLevel"
          :items="levels"
          :label="t('courses.level')"
          variant="outlined"
          density="compact"
          clearable
          hide-details
        />
      </VCol>

      <VCol cols="6" md="2">
        <VSelect
          v-model="priceFilter"
          :items="priceOptions"
          :label="t('courses.price')"
          variant="outlined"
          density="compact"
          hide-details
        />
      </VCol>

      <VCol cols="12" md="4">
        <VSelect
          v-model="sortBy"
          :items="sortOptions"
          :label="t('common.sort_by')"
          variant="outlined"
          density="compact"
          hide-details
        />
      </VCol>
    </VRow>

    <!-- Loading -->
    <VRow v-if="coursesStore.loading">
      <VCol v-for="n in 6" :key="n" cols="12" sm="6" lg="4">
        <VSkeletonLoader type="card" />
      </VCol>
    </VRow>

    <!-- Courses Grid -->
    <VRow v-else-if="coursesStore.catalogCourses.length > 0">
      <VCol
        v-for="course in coursesStore.catalogCourses"
        :key="course.id"
        cols="12"
        sm="6"
        lg="4"
      >
        <VCard
          class="course-card h-100"
          :to="{ name: 'course-detail', params: { slug: course.slug } }"
        >
          <VImg
            :src="course.thumbnail || '/images/course-placeholder.jpg'"
            height="180"
            cover
            class="course-thumbnail"
          >
            <template #placeholder>
              <VRow class="fill-height ma-0" align="center" justify="center">
                <VProgressCircular indeterminate color="primary" />
              </VRow>
            </template>

            <!-- Level Badge -->
            <VChip
              size="small"
              :color="getLevelColor(course.level)"
              class="ma-2 position-absolute"
              style="top: 0; left: 0;"
            >
              {{ course.level_label }}
            </VChip>

            <!-- Price Badge -->
            <VChip
              size="small"
              :color="course.price === 0 ? 'success' : 'primary'"
              class="ma-2 position-absolute"
              style="top: 0; right: 0;"
            >
              {{ course.price === 0 ? 'Gratis' : course.formatted_price }}
            </VChip>
          </VImg>

          <VCardTitle class="text-subtitle-1 font-weight-medium">
            {{ course.title }}
          </VCardTitle>

          <VCardText>
            <p class="text-body-2 text-medium-emphasis mb-3 course-description">
              {{ course.short_description }}
            </p>

            <div class="d-flex align-center gap-4 text-caption text-medium-emphasis">
              <span>
                <VIcon size="small" icon="mdi-play-circle-outline" class="me-1" />
                {{ course.total_topics }} lecciones
              </span>
              <span>
                <VIcon size="small" icon="mdi-clock-outline" class="me-1" />
                {{ course.duration_hours }}h
              </span>
              <span v-if="course.rating">
                <VIcon size="small" icon="mdi-star" color="warning" class="me-1" />
                {{ course.rating }}
              </span>
            </div>
          </VCardText>

          <VCardActions>
            <VBtn
              color="primary"
              variant="tonal"
              block
            >
              Ver curso
            </VBtn>
          </VCardActions>
        </VCard>
      </VCol>
    </VRow>

    <!-- Empty State -->
    <VRow v-else>
      <VCol cols="12">
        <VCard class="text-center pa-8">
          <VIcon icon="mdi-school-outline" size="64" color="grey" />
          <h3 class="text-h6 mt-4">{{ t('courses.no_courses_found') }}</h3>
          <p class="text-body-2 text-medium-emphasis">
            {{ t('courses.try_different_filters') }}
          </p>
        </VCard>
      </VCol>
    </VRow>

    <!-- Pagination -->
    <VRow v-if="coursesStore.pagination.lastPage > 1" class="mt-4">
      <VCol cols="12" class="d-flex justify-center">
        <VPagination
          :model-value="coursesStore.pagination.currentPage"
          :length="coursesStore.pagination.lastPage"
          :total-visible="5"
          @update:model-value="handlePageChange"
        />
      </VCol>
    </VRow>
  </VContainer>
</template>

<script lang="ts">
function getLevelColor(level: string): string {
  const colors: Record<string, string> = {
    beginner: 'success',
    intermediate: 'warning',
    advanced: 'error',
  }
  return colors[level] || 'grey'
}
</script>

<style scoped>
.course-card {
  transition: transform 0.2s, box-shadow 0.2s;
  cursor: pointer;
}

.course-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.course-description {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  min-height: 40px;
}
</style>
