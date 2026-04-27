<template>
  <VContainer>
    <div class="d-flex align-center mb-6">
      <VBtn icon="mdi-arrow-left" variant="text" to="/admin/courses" class="mr-2" />
      <div>
        <h1 class="text-h4 font-weight-bold">Contenido del Curso</h1>
        <p class="text-subtitle-1 text-grey" v-if="course">{{ course.title }}</p>
      </div>
      <VSpacer />
      <VBtn color="primary" prepend-icon="mdi-plus" @click="addBlock">
        Añadir Bloque
      </VBtn>
    </div>

    <VRow v-if="loading">
      <VCol cols="12" class="text-center py-12">
        <VProgressCircular indeterminate />
      </VCol>
    </VRow>

    <template v-else-if="course">
      <VExpansionPanels v-model="openedPanels" multiple class="mb-6">
        <VExpansionPanel
          v-for="(block, bIndex) in course.blocks"
          :key="block.id"
          class="mb-4 border"
        >
          <VExpansionPanelTitle>
            <div class="d-flex align-center w-100 mr-4">
              <VIcon icon="mdi-drag-vertical" class="mr-2 text-grey" />
              <span class="font-weight-bold">{{ block.title }}</span>
              <VSpacer />
              <VBtn
                icon="mdi-plus"
                size="x-small"
                variant="tonal"
                color="success"
                class="mr-2"
                @click.stop="addTopic(block)"
                title="Añadir tema"
              />
              <VBtn
                icon="mdi-pencil"
                size="x-small"
                variant="text"
                color="primary"
                class="mr-2"
                @click.stop="editBlock(block)"
              />
              <VBtn
                icon="mdi-delete"
                size="x-small"
                variant="text"
                color="error"
                @click.stop="confirmDeleteBlock(block)"
              />
            </div>
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <VList lines="two" density="compact">
              <VListItem
                v-for="(topic, tIndex) in block.topics"
                :key="topic.id"
                class="border-bottom"
              >
                <template #prepend>
                  <VIcon :icon="getTopicIcon(topic.content_type)" class="mr-3" />
                </template>
                <VListItemTitle>{{ topic.title }}</VListItemTitle>
                <VListItemSubtitle>{{ topic.content_type.toUpperCase() }}</VListItemSubtitle>
                <template #append>
                  <VBtn
                    icon="mdi-pencil"
                    size="x-small"
                    variant="text"
                    color="primary"
                    @click="editTopic(topic, block)"
                  />
                  <VBtn
                    icon="mdi-delete"
                    size="x-small"
                    variant="text"
                    color="error"
                    @click="confirmDeleteTopic(topic, block)"
                  />
                </template>
              </VListItem>
              <VListItem v-if="!block.topics?.length" class="text-center text-grey py-4">
                No hay temas en este bloque.
              </VListItem>
            </VList>
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </template>

    <!-- Dialogs for CRUD (Placeholders for now) -->
    <VDialog v-model="dialog.show" max-width="500">
      <VCard>
        <VCardTitle>{{ dialog.title }}</VCardTitle>
        <VCardText>
          <VTextField v-model="dialog.data.title" label="Título" />
          <VSelect
            v-if="dialog.type === 'topic'"
            v-model="dialog.data.content_type"
            :items="['video', 'pdf', 'text']"
            label="Tipo de contenido"
          />
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="dialog.show = false">Cancelar</VBtn>
          <VBtn color="primary" @click="saveDialog">Guardar</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import draggable from 'vuedraggable'
import api from '@/api'
import type { Course, CourseBlock, CourseTopic } from '@/types/courses'

const route = useRoute()
const loading = ref(false)
const course = ref<Course | null>(null)
const openedPanels = ref<number[]>([])

const dialog = ref({
  show: false,
  title: '',
  type: 'block' as 'block' | 'topic',
  data: {} as any
})

onMounted(fetchCourseContent)

async function fetchCourseContent() {
  const courseId = route.params.id
  loading.value = true
  try {
    const { data } = await api.get(`/courses/${courseId}`)
    course.value = data.data
    // Open first block by default
    if (course.value?.blocks?.length) {
      openedPanels.value = [0]
    }
  } catch (error) {
    console.error('Error fetching course content:', error)
  } finally {
    loading.value = false
  }
}

function getTopicIcon(type: string) {
  switch (type) {
    case 'video': return 'mdi-play-circle'
    case 'pdf': return 'mdi-file-pdf-box'
    default: return 'mdi-text-box'
  }
}

// Dialog Actions
function addBlock() {
  dialog.value = {
    show: true,
    title: 'Nuevo Bloque',
    type: 'block',
    data: { title: '' }
  }
}

function addTopic(block: CourseBlock) {
  dialog.value = {
    show: true,
    title: `Nuevo Tema en ${block.title}`,
    type: 'topic',
    data: { title: '', content_type: 'video', block_id: block.id }
  }
}

function editBlock(block: CourseBlock) {
  dialog.value = {
    show: true,
    title: 'Editar Bloque',
    type: 'block',
    data: { ...block }
  }
}

function editTopic(topic: CourseTopic, block: CourseBlock) {
  dialog.value = {
    show: true,
    title: 'Editar Tema',
    type: 'topic',
    data: { ...topic }
  }
}

async function saveDialog() {
  // Logic to call API and refresh course content
  dialog.value.show = false
  fetchCourseContent()
}

function confirmDeleteBlock(block: CourseBlock) { /* ... */ }
function confirmDeleteTopic(topic: CourseTopic, block: CourseBlock) { /* ... */ }
</script>
 CourseBlock) {
  dialog.value = {
    show: true,
    title: 'Editar Bloque',
    type: 'block',
    data: { ...block }
  }
}

function editTopic(topic: CourseTopic, block: CourseBlock) {
  dialog.value = {
    show: true,
    title: 'Editar Tema',
    type: 'topic',
    data: { ...topic }
  }
}

async function saveDialog() {
  // Logic to call API and refresh course content
  dialog.value.show = false
  fetchCourseContent()
}

function confirmDeleteBlock(block: CourseBlock) { /* ... */ }
function confirmDeleteTopic(topic: CourseTopic, block: CourseBlock) { /* ... */ }
</script>
