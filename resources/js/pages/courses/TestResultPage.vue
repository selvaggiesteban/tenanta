<template>
  <VContainer>
    <VRow v-if="loading">
      <VCol cols="12" class="text-center">
        <VProgressCircular indeterminate />
      </VCol>
    </VRow>

    <template v-else-if="result">
      <VRow justify="center">
        <VCol cols="12" md="8">
          <!-- Score Card -->
          <VCard class="mb-4">
            <VCardText class="text-center pa-8">
              <VAvatar
                :color="result.passed ? 'success' : 'error'"
                size="120"
                class="mb-4"
              >
                <VIcon
                  :icon="result.passed ? 'mdi-check-circle' : 'mdi-close-circle'"
                  size="64"
                />
              </VAvatar>

              <h1 class="text-h4 mb-2">
                {{ result.passed ? '¡Aprobado!' : 'No aprobado' }}
              </h1>
              <p class="text-h5">
                Puntaje: <strong>{{ result.score }}/{{ result.totalPoints }}</strong>
                ({{ percentage }}%)
              </p>
              <p class="text-body-1 text-grey">
                Se requiere {{ result.test.passingScore }}% para aprobar
              </p>

              <VProgressLinear
                :model-value="percentage"
                :color="result.passed ? 'success' : 'error'"
                height="20"
                class="mt-4"
                rounded
              />
            </VCardText>
          </VCard>

          <!-- Details -->
          <VCard v-if="result.test.showAnswersAfter">
            <VCardTitle>Revisión de respuestas</VCardTitle>
            <VCardText>
              <VExpansionPanels>
                <VExpansionPanel
                  v-for="(question, index) in result.test.questions"
                  :key="question.id"
                >
                  <VExpansionPanelTitle>
                    <VIcon
                      :icon="getQuestionIcon(question)"
                      :color="getQuestionColor(question)"
                      class="mr-2"
                    />
                    Pregunta {{ index + 1 }}
                  </VExpansionPanelTitle>
                  <VExpansionPanelText>
                    <p class="font-weight-medium">{{ question.question }}</p>
                    <p class="text-body-2 text-grey">{{ question.explanation }}</p>

                    <VDivider class="my-2" />

                    <div
                      v-for="option in question.options"
                      :key="option.id"
                      class="d-flex align-center py-1"
                      :class="getOptionClass(option, question)"
                    >
                      <VIcon
                        :icon="getOptionIcon(option, question)"
                        size="small"
                        class="mr-2"
                      />
                      {{ option.text }}
                    </div>

                    <p v-if="question.userAnswer !== undefined" class="mt-2">
                      <strong>Tu respuesta:</strong>
                      {{ formatUserAnswer(question.userAnswer, question) }}
                    </p>
                  </VExpansionPanelText>
                </VExpansionPanel>
              </VExpansionPanels>
            </VCardText>
          </VCard>

          <!-- Actions -->
          <VCard class="mt-4">
            <VCardActions class="justify-center">
              <VBtn
                color="primary"
                @click="$router.push('/my-courses')"
              >
                <VIcon start icon="mdi-book-open" />
                Mis Cursos
              </VBtn>
              <VBtn
                v-if="!result.passed && canRetry"
                color="secondary"
                @click="retryTest"
              >
                <VIcon start icon="mdi-refresh" />
                Reintentar
              </VBtn>
            </VCardActions>
          </VCard>
        </VCol>
      </VRow>
    </template>

    <VRow v-else>
      <VCol cols="12" class="text-center">
        <VAlert type="error">
          No se pudo cargar el resultado
        </VAlert>
      </VCol>
    </VRow>
  </VContainer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCoursesStore } from '@/stores/courses'

const route = useRoute()
const router = useRouter()
const coursesStore = useCoursesStore()

const loading = ref(false)
const result = ref<any>(null)

const percentage = computed(() => {
  if (!result.value) return 0
  return Math.round((result.value.score / result.value.totalPoints) * 100)
})

const canRetry = computed(() => {
  if (!result.value) return false
  return result.value.attemptNumber < result.value.test.maxAttempts
})

onMounted(async () => {
  await loadResult()
})

async function loadResult() {
  const testId = parseInt(route.params.id as string)
  const attemptId = parseInt(route.params.attemptId as string)
  if (!testId || !attemptId) return

  loading.value = true
  try {
    result.value = await coursesStore.fetchTestResult(testId, attemptId)
  } finally {
    loading.value = false
  }
}

function getQuestionIcon(question: any): string {
  const isCorrect = isQuestionCorrect(question)
  return isCorrect ? 'mdi-check-circle' : 'mdi-close-circle'
}

function getQuestionColor(question: any): string {
  const isCorrect = isQuestionCorrect(question)
  return isCorrect ? 'success' : 'error'
}

function isQuestionCorrect(question: any): boolean {
  const correctOptions = question.options.filter((o: any) => o.isCorrect).map((o: any) => o.id)
  const userAnswer = question.userAnswer

  if (Array.isArray(userAnswer)) {
    return JSON.stringify(userAnswer.sort()) === JSON.stringify(correctOptions.sort())
  }
  return userAnswer === correctOptions[0]
}

function getOptionClass(option: any, question: any): string {
  const isCorrect = option.isCorrect
  const isSelected = isOptionSelected(option.id, question.userAnswer)

  if (isCorrect) return 'text-success font-weight-medium'
  if (isSelected && !isCorrect) return 'text-error'
  return 'text-grey'
}

function getOptionIcon(option: any, question: any): string {
  const isCorrect = option.isCorrect
  const isSelected = isOptionSelected(option.id, question.userAnswer)

  if (isCorrect) return 'mdi-check-circle'
  if (isSelected && !isCorrect) return 'mdi-close-circle'
  return 'mdi-circle-outline'
}

function isOptionSelected(optionId: number, userAnswer: any): boolean {
  if (Array.isArray(userAnswer)) {
    return userAnswer.includes(optionId)
  }
  return userAnswer === optionId
}

function formatUserAnswer(answer: any, question: any): string {
  if (answer === undefined || answer === null) return 'Sin respuesta'

  if (question.type === 'true_false') {
    return answer ? 'Verdadero' : 'Falso'
  }

  if (Array.isArray(answer)) {
    return answer.map((id: number) => {
      const option = question.options.find((o: any) => o.id === id)
      return option ? option.text : id
    }).join(', ')
  }

  const option = question.options.find((o: any) => o.id === answer)
  return option ? option.text : String(answer)
}

async function retryTest() {
  const testId = parseInt(route.params.id as string)
  router.push(`/test/${testId}`)
}
</script>
