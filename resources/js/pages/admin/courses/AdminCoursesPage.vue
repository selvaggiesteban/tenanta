<template>
  <VContainer>
    <div class="d-flex align-center justify-space-between mb-6">
      <h1 class="text-h4 font-weight-bold">Gestión de Cursos</h1>
      <VBtn color="primary" prepend-icon="mdi-plus" @click="createCourse">
        Nuevo Curso
      </VBtn>
    </div>

    <VCard>
      <VCardTitle>
        <VTextField
          v-model="search"
          append-inner-icon="mdi-magnify"
          label="Buscar cursos..."
          single-line
          hide-details
          density="compact"
        />
      </VCardTitle>

      <VDataTable
        :headers="headers"
        :items="courses"
        :loading="loading"
        :search="search"
        hover
      >
        <template #[`item.status`]="{ item }">
          <VChip
            :color="getStatusColor(item.status)"
            size="small"
            variant="tonal"
          >
            {{ item.status.toUpperCase() }}
          </VChip>
        </template>

        <template #[`item.price`]="{ item }">
          {{ formatPrice(item.price) }}
        </template>

        <template #[`item.actions`]="{ item }">
          <div class="d-flex gap-2">
            <VBtn
              icon="mdi-pencil"
              variant="text"
              size="small"
              color="primary"
              :to="`/admin/courses/${item.id}/content`"
              title="Editar contenido"
            />
            <VBtn
              icon="mdi-cog"
              variant="text"
              size="small"
              color="secondary"
              @click="editSettings(item)"
              title="Configuración"
            />
            <VBtn
              icon="mdi-delete"
              variant="text"
              size="small"
              color="error"
              @click="confirmDelete(item)"
              title="Eliminar"
            />
          </div>
        </template>
      </VDataTable>
    </VCard>

    <!-- Delete Confirmation Dialog -->
    <VDialog v-model="deleteDialog" max-width="400">
      <VCard>
        <VCardTitle class="text-h5">¿Eliminar curso?</VCardTitle>
        <VCardText>
          Esta acción no se puede deshacer. Se eliminarán todos los bloques y temas asociados.
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn color="grey" variant="text" @click="deleteDialog = false">Cancelar</VBtn>
          <VBtn color="error" variant="text" @click="doDelete" :loading="deleting">Eliminar</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/api'
import type { Course } from '@/types/courses'

const search = ref('')
const loading = ref(false)
const courses = ref<Course[]>([])
const deleteDialog = ref(false)
const deleting = ref(false)
const selectedCourse = ref<Course | null>(null)

const headers = [
  { title: 'Título', key: 'title' },
  { title: 'Instructor', key: 'instructor.name' },
  { title: 'Estado', key: 'status' },
  { title: 'Precio', key: 'price' },
  { title: 'Acciones', key: 'actions', sortable: false },
]

onMounted(fetchCourses)

async function fetchCourses() {
  loading.value = true
  try {
    const { data } = await api.get('/courses')
    courses.value = data.data
  } catch (error) {
    console.error('Error fetching courses:', error)
  } finally {
    loading.value = false
  }
}

function getStatusColor(status: string) {
  switch (status) {
    case 'published': return 'success'
    case 'draft': return 'warning'
    case 'archived': return 'grey'
    default: return 'primary'
  }
}

function formatPrice(price: number) {
  return new Intl.NumberFormat('es-AR', { style: 'currency', currency: 'ARS' }).format(price)
}

function createCourse() {
  // Logic to open create modal or redirect
}

function editSettings(course: Course) {
  // Logic to edit course settings
}

function confirmDelete(course: Course) {
  selectedCourse.value = course
  deleteDialog.value = true
}

async function doDelete() {
  if (!selectedCourse.value) return
  deleting.value = true
  try {
    await api.delete(`/courses/${selectedCourse.value.id}`)
    courses.value = courses.value.filter(c => c.id !== selectedCourse.value?.id)
    deleteDialog.value = false
  } catch (error) {
    console.error('Error deleting course:', error)
  } finally {
    deleting.value = false
  }
}
</script>
