<template>
  <VContainer>
    <VRow v-if="loading">
      <VCol cols="12" class="text-center">
        <VProgressCircular indeterminate />
      </VCol>
    </VRow>

    <template v-else-if="test && currentAttempt">
      <VRow>
        <VCol cols="12" md="8">
          <VCard>
            <VCardTitle class="d-flex align-center">
              <span>{{ test.title }}</span>
              <VSpacer />
              <TestTimer
                v-if="test.time_limit_minutes"
                :duration="test.time_limit_minutes * 60"
                :start-time="currentAttempt.started_at"
                @timeout="handleTimeout"
              />
            </VCardTitle>
            <VCardSubtitle>
              Pregunta {{ currentQuestionIndex + 1 }} de {{ test.questions?.length || 0 }}
            </VCardSubtitle>
            <VCardText>
              <QuizQuestion
                v-if="currentQuestion"
                :question="currentQuestion"
                :model-value="answers[currentQuestion.id] || []"
                @update:model-value="updateAnswer"
              />
            </VCardText>
            <VCardActions>
              <VBtn
                :disabled="currentQuestionIndex === 0"
                @click="previousQuestion"
              >
                <VIcon start icon="mdi-arrow-left" />
                Anterior
              </VBtn>
              <VSpacer />
              <VBtn
                v-if="test.questions && currentQuestionIndex < test.questions.length - 1"
                color="primary"
                @click="nextQuestion"
              >
                Siguiente
                <VIcon end icon="mdi-arrow-right" />
              </VBtn>
              <VBtn
                v-else
                color="success"
                :loading="submitting"
                @click="submitTest"
              >
                <VIcon start icon="mdi-check" />
                Finalizar
              </VBtn>
            </VCardActions>
          </VCard>
        </VCol>

        <VCol cols="12" md="4">
          <VCard>
            <VCardTitle>Progreso</VCardTitle>
            <VCardText>
              <VRow dense>
                <VCol
                v-for="(question, index) in test.questions"
                :key="question.id"
                cols="3"
              >
                  <VBtn
                    :color="getQuestionColor(question.id, index)"
                    size="small"
                    block
                    @click="goToQuestion(index)"
                  >
                    {{ index + 1 }}
                  </VBtn>
                </VCol>
              </VRow>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </template>

    <VRow v-else>
      <VCol cols="12" class="text-center">
        <VAlert type="error">
          No se pudo cargar el test
        </VAlert>
      </VCol>
    </VRow>
  </VContainer>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import QuizQuestion from '@/components/courses/QuizQuestion.vue'
import TestTimer from '@/components/courses/TestTimer.vue'
import { useCoursesStore } from '@/stores/courses'
import type { CourseTest, TestAttempt, AttemptQuestion } from '@/types/courses'

const route = useRoute()
const router = useRouter()
const coursesStore = useCoursesStore()

const loading = ref(false)
const submitting = ref(false)
const test = ref<CourseTest | null>(null)
const currentAttempt = ref<TestAttempt | null>(null)
const answers = ref<Record<number, number[]>>({})
const currentQuestionIndex = ref(0)

const currentQuestion = computed<AttemptQuestion | null>(() => {
  if (!test.value?.questions) return null
  return test.value.questions[currentQuestionIndex.value]
})

onMounted(async () => {
  await loadTest()
})

onBeforeUnmount(() => {
  saveProgress()
})

async function loadTest() {
  const testId = parseInt(route.params.id as string)
  if (!testId) return

  loading.value = true
  try {
    const attempt = await coursesStore.startTest(testId)
    currentAttempt.value = attempt
    test.value = await coursesStore.fetchTest(testId)
  } finally {
    loading.value = false
  }
}

function updateAnswer(value: number[]) {
  if (currentQuestion.value) {
    answers.value[currentQuestion.value.id] = value
  }
}

function nextQuestion() {
  if (test.value?.questions && currentQuestionIndex.value < test.value.questions.length - 1) {
    currentQuestionIndex.value++
  }
}

function previousQuestion() {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
  }
}

function goToQuestion(index: number) {
  currentQuestionIndex.value = index
}

function getQuestionColor(questionId: number, index: number): string {
  if (index === currentQuestionIndex.value) return 'primary'
  if (answers.value[questionId] !== undefined) return 'success'
  return 'grey'
}

function saveProgress() {
  // TODO: Implement progress save endpoint
}

async function handleTimeout() {
  await submitTest()
}

async function submitTest() {
  if (!test.value?.id || !currentAttempt.value?.id) return

  submitting.value = true
  try {
    const result = await coursesStore.submitTest(
      test.value.id,
      currentAttempt.value.id,
      answers.value
    )
    router.push(`/test/${test.value.id}/result/${result.attemptId}`)
  } finally {
    submitting.value = false
  }
}
</script>
