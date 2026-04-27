<template>
  <VContainer>
    <div class="d-flex align-center mb-6">
      <VBtn icon="mdi-arrow-left" variant="text" :to="`/admin/courses/${courseId}/content`" class="mr-2" />
      <div>
        <h1 class="text-h4 font-weight-bold">Gestión de Test</h1>
        <p class="text-subtitle-1 text-grey" v-if="test">{{ test.title }}</p>
      </div>
      <VSpacer />
      <VBtn color="primary" prepend-icon="mdi-plus" @click="addQuestion">
        Añadir Pregunta
      </VBtn>
    </div>

    <VRow v-if="loading">
      <VCol cols="12" class="text-center py-12">
        <VProgressCircular indeterminate />
      </VCol>
    </VRow>

    <template v-else-if="test">
      <VExpansionPanels multiple class="mb-6">
        <VExpansionPanel
          v-for="(question, qIndex) in test.questions"
          :key="question.id"
          class="mb-4 border"
        >
          <VExpansionPanelTitle>
            <div class="d-flex align-center w-100 mr-4">
              <span class="font-weight-bold">Pregunta {{ qIndex + 1 }}: {{ truncate(question.question, 50) }}</span>
              <VSpacer />
              <VChip size="x-small" class="mr-2">{{ question.type.toUpperCase() }}</VChip>
              <VBtn
                icon="mdi-pencil"
                size="x-small"
                variant="text"
                color="primary"
                class="mr-2"
                @click.stop="editQuestion(question)"
              />
              <VBtn
                icon="mdi-delete"
                size="x-small"
                variant="text"
                color="error"
                @click.stop="confirmDeleteQuestion(question)"
              />
            </div>
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <div class="pa-2">
              <VList density="compact">
                <VListItem
                  v-for="option in question.options"
                  :key="option.id"
                  :prepend-icon="option.is_correct ? 'mdi-check-circle' : 'mdi-circle-outline'"
                  :class="{ 'text-success font-weight-bold': option.is_correct }"
                >
                  <VListItemTitle>{{ option.text }}</VListItemTitle>
                </VListItem>
              </VList>
            </div>
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </template>

    <!-- Question Editor Dialog -->
    <VDialog v-model="editor.show" max-width="800" persistent>
      <VCard>
        <VCardTitle>{{ editor.isNew ? 'Nueva Pregunta' : 'Editar Pregunta' }}</VCardTitle>
        <VCardText>
          <VRow>
            <VCol cols="12">
              <VTextarea v-model="editor.data.question" label="Pregunta" rows="2" />
            </VCol>
            <VCol cols="12" md="6">
              <VSelect
                v-model="editor.data.type"
                :items="[
                  { title: 'Opción Única', value: 'single' },
                  { title: 'Múltiple Opción', value: 'multiple' },
                  { title: 'Verdadero / Falso', value: 'true_false' }
                ]"
                label="Tipo de Pregunta"
              />
            </VCol>
          </VRow>

          <VDivider class="my-4" />

          <div class="d-flex align-center justify-space-between mb-4">
            <h3 class="text-h6">Opciones</h3>
            <VBtn
              v-if="editor.data.type !== 'true_false'"
              size="small"
              color="success"
              prepend-icon="mdi-plus"
              @click="addOption"
            >
              Añadir Opción
            </VBtn>
          </div>

          <VRow v-for="(option, oIndex) in editor.data.options" :key="oIndex" align="center" dense>
            <VCol cols="auto">
              <VCheckbox
                v-model="option.is_correct"
                hide-details
                @update:model-value="handleCorrectChange(oIndex)"
              />
            </VCol>
            <VCol>
              <VTextField v-model="option.text" label="Texto de la opción" hide-details />
            </VCol>
            <VCol cols="auto" v-if="editor.data.type !== 'true_false'">
              <VBtn icon="mdi-delete" variant="text" color="error" size="small" @click="removeOption(oIndex)" />
            </VCol>
          </VRow>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="editor.show = false">Cancelar</VBtn>
          <VBtn color="primary" @click="saveQuestion" :loading="saving">Guardar Pregunta</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </VContainer>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/api'
import type { Test, Question } from '@/types/courses'

const route = useRoute()
const courseId = ref(route.params.courseId)
const testId = ref(route.params.id)
const loading = ref(false)
const saving = ref(false)
const test = ref<Test | null>(null)

const editor = ref({
  show: false,
  isNew: true,
  data: {
    question: '',
    type: 'single',
    options: [] as any[]
  }
})

onMounted(fetchTestContent)

async function fetchTestContent() {
  loading.value = true
  try {
    const { data } = await api.get(`/tests/${testId.value}`)
    test.value = data.data
  } catch (error) {
    console.error('Error fetching test content:', error)
  } finally {
    loading.value = false
  }
}

function truncate(text: string, length: number) {
  return text.length > length ? text.substring(0, length) + '...' : text
}

function addQuestion() {
  editor.value = {
    show: true,
    isNew: true,
    data: {
      question: '',
      type: 'single',
      options: [
        { text: '', is_correct: true },
        { text: '', is_correct: false }
      ]
    }
  }
}

function editQuestion(question: Question) {
  editor.value = {
    show: true,
    isNew: false,
    data: JSON.parse(JSON.stringify(question))
  }
}

function addOption() {
  editor.value.data.options.push({ text: '', is_correct: false })
}

function removeOption(index: number) {
  editor.value.data.options.splice(index, 1)
}

function handleCorrectChange(index: number) {
  if (editor.value.data.type === 'single' || editor.value.data.type === 'true_false') {
    editor.value.data.options.forEach((opt, i) => {
      if (i !== index) opt.is_correct = false
    })
  }
}

async function saveQuestion() {
  saving.value = true
  try {
    if (editor.value.isNew) {
      await api.post(`/tests/${testId.value}/questions`, editor.value.data)
    } else {
      await api.put(`/questions/${(editor.value.data as any).id}`, editor.value.data)
    }
    editor.value.show = false
    fetchTestContent()
  } catch (error) {
    console.error('Error saving question:', error)
  } finally {
    saving.value = false
  }
}

function confirmDeleteQuestion(question: Question) { /* ... */ }
</script>
