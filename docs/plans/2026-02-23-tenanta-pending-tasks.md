# Tenanta Pending Tasks Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Complete all pending tasks from CLAUDE.md: Course tests frontend, Public frontend, Payment system, Dual versions (ES/EN), Course admin, AI integration, and testing documentation.

**Architecture:** Multi-tenant SaaS with Laravel 11 + Vue 3. Following existing patterns: BelongsToTenant trait, API Resources, Form Requests, Pinia stores. All new code must match existing code style (no semicolons, 2-space indent, camelCase).

**Tech Stack:** Laravel 11, PHP 8.3, Vue 3, Vuetify 3, TypeScript, Pinia, MercadoPago API

---

## Overview of Tasks

### Phase 2: Course Tests Frontend (2 tasks)
- TestPage.vue - Take evaluations
- TestResultPage.vue - View test results

### Phase 5: Public Frontend (13 tasks)
- Routes architecture + PublicLayout.vue
- PublicHeader.vue, PublicFooter.vue
- HeroSection.vue, FeatureCard.vue, TestimonialCard.vue
- ContactForm.vue, PricingCard.vue
- HomePage.vue, PricingPage.vue, ContactPage.vue, AboutPage.vue, PrivacyPage.vue, TermsPage.vue
- PublicController.php, PublicInquiryController.php

### Phase 6: Payment System (8 tasks)
- PaymentServiceInterface.php
- MercadoPagoService.php
- PaymentManager.php
- PaymentController.php + webhooks
- CheckoutPage.vue, PaymentSuccessPage.vue, PaymentFailurePage.vue

### Phase 7: Dual Versions ES/EN (4 tasks)
- Copy tenanta/ to tenanta_en/
- Update .env configuration
- Translate lang/ files
- Translate resources/js/locales/

### Phase 8 & 9: Course Administration (16 tasks)
- AdminCourseController.php, AdminBlockController.php, AdminTopicController.php
- AdminTestController.php, AdminSubscriptionPlanController.php, AdminEnrollmentController.php
- AdminCoursesPage.vue, AdminCourseFormPage.vue, AdminCourseContentPage.vue
- AdminTestFormPage.vue, AdminSubscriptionPlansPage.vue, AdminEnrollmentsPage.vue
- CourseBlockEditor.vue, TopicEditor.vue, VideoUploader.vue, PdfUploader.vue
- TestQuestionEditor.vue, EnrollmentTable.vue

### Phase 10: Integration (3 tasks)
- Add AI tools to ToolDefinitions.php
- Integrate metrics into Dashboard
- testing.md documentation

**Total Tasks: 46 bite-sized tasks**

---

## PHASE 2: Course Tests Frontend

### Task 1: Create TestPage.vue

**Files:**
- Create: `resources/js/pages/courses/TestPage.vue`

**Step 1: Write the component**

```vue
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
                v-if="test.timeLimitMinutes"
                :duration="test.timeLimitMinutes * 60"
                :start-time="currentAttempt.startedAt"
                @timeout="handleTimeout"
              />
            </VCardTitle>
            <VCardSubtitle>
              Pregunta {{ currentQuestionIndex + 1 }} de {{ test.questions.length }}
            </VCardSubtitle>
            <VCardText>
              <QuizQuestion
                v-if="currentQuestion"
                :question="currentQuestion"
                :value="answers[currentQuestion.id]"
                @update:value="updateAnswer"
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
                v-if="currentQuestionIndex < test.questions.length - 1"
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

const route = useRoute()
const router = useRouter()
const coursesStore = useCoursesStore()

const loading = ref(false)
const submitting = ref(false)
const test = ref<any>(null)
const currentAttempt = ref<any>(null)
const answers = ref<Record<number, any>>({})
const currentQuestionIndex = ref(0)

const currentQuestion = computed(() => {
  if (!test.value) return null
  return test.value.questions[currentQuestionIndex.value]
})

onMounted(async () => {
  await loadTest()
})

onBeforeUnmount(() => {
  // Auto-save answers when leaving
  saveProgress()
})

async function loadTest() {
  const testId = parseInt(route.params.id as string)
  if (!testId) return

  loading.value = true
  try {
    // Start attempt
    const attempt = await coursesStore.startTest(testId)
    currentAttempt.value = attempt

    // Load test with questions
    test.value = await coursesStore.fetchTest(testId)
  } finally {
    loading.value = false
  }
}

function updateAnswer(questionId: number, value: any) {
  answers.value[questionId] = value
}

function nextQuestion() {
  if (currentQuestionIndex.value < test.value.questions.length - 1) {
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
```

**Step 2: Add missing QuizQuestion and TestTimer components**

Create `resources/js/components/courses/QuizQuestion.vue`:

```vue
<template>
  <div>
    <h3 class="text-h6 mb-4">{{ question.question }}</h3>
    <p v-if="question.explanation" class="text-body-2 text-grey mb-4">
      {{ question.explanation }}
    </p>

    <VRadioGroup
      v-if="question.type === 'single'"
      :model-value="modelValue"
      @update:model-value="$emit('update:value', $event)"
    >
      <VRadio
        v-for="option in question.options"
        :key="option.id"
        :label="option.text"
        :value="option.id"
      />
    </VRadioGroup>

    <template v-else-if="question.type === 'multiple'">
      <VCheckbox
        v-for="option in question.options"
        :key="option.id"
        :model-value="isSelected(option.id)"
        :label="option.text"
        @update:model-value="toggleOption(option.id)"
      />
    </template>

    <VRadioGroup
      v-else-if="question.type === 'true_false'"
      :model-value="modelValue"
      @update:model-value="$emit('update:value', $event)"
    >
      <VRadio label="Verdadero" :value="true" />
      <VRadio label="Falso" :value="false" />
    </VRadioGroup>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  question: any
  value: any
}>()

const emit = defineEmits<{
  (e: 'update:value', value: any): void
}>()

const modelValue = computed(() => props.value)

function isSelected(optionId: number): boolean {
  return Array.isArray(props.value) && props.value.includes(optionId)
}

function toggleOption(optionId: number) {
  const current = Array.isArray(props.value) ? [...props.value] : []
  const index = current.indexOf(optionId)
  if (index > -1) {
    current.splice(index, 1)
  } else {
    current.push(optionId)
  }
  emit('update:value', current)
}
</script>
```

Create `resources/js/components/courses/TestTimer.vue`:

```vue
<template>
  <VChip
    :color="timeColor"
    size="large"
  >
    <VIcon start icon="mdi-clock" />
    {{ formattedTime }}
  </VChip>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps<{
  duration: number // in seconds
  startTime: string
}>()

const emit = defineEmits<{
  (e: 'timeout'): void
}>()

const remaining = ref(0)
let interval: ReturnType<typeof setInterval> | null = null

const formattedTime = computed(() => {
  const minutes = Math.floor(remaining.value / 60)
  const seconds = remaining.value % 60
  return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`
})

const timeColor = computed(() => {
  if (remaining.value < 60) return 'error'
  if (remaining.value < 300) return 'warning'
  return 'primary'
})

onMounted(() => {
  const start = new Date(props.startTime).getTime()
  const end = start + props.duration * 1000

  interval = setInterval(() => {
    const now = Date.now()
    remaining.value = Math.max(0, Math.floor((end - now) / 1000))

    if (remaining.value === 0) {
      if (interval) clearInterval(interval)
      emit('timeout')
    }
  }, 1000)
})

onUnmounted(() => {
  if (interval) clearInterval(interval)
})
</script>
```

**Step 3: Update courses store**

Modify `resources/js/stores/courses.ts`, add:

```typescript
async startTest(testId: number): Promise<any> {
  this.loading = true
  try {
    const { data } = await api.post(`/tests/${testId}/start`)
    return data.data
  } finally {
    this.loading = false
  }
},

async fetchTest(testId: number): Promise<any> {
  this.loading = true
  try {
    const { data } = await api.get(`/tests/${testId}`)
    return data.data
  } finally {
    this.loading = false
  }
},

async submitTest(testId: number, attemptId: number, answers: Record<number, any>): Promise<any> {
  this.loading = true
  try {
    const { data } = await api.post(`/tests/${testId}/submit`, {
      attemptId,
      answers
    })
    return data.data
  } finally {
    this.loading = false
  }
}
```

**Step 4: Add route**

Modify `resources/js/router/index.ts`, add to routes:

```typescript
{
  path: '/test/:id',
  component: () => import('@/pages/courses/TestPage.vue'),
  meta: { requiresAuth: true }
}
```

**Step 5: Commit**

```bash
git add resources/js/pages/courses/TestPage.vue resources/js/components/courses/QuizQuestion.vue resources/js/components/courses/TestTimer.vue resources/js/stores/courses.ts resources/js/router/index.ts
git commit -m "feat(courses): add TestPage.vue for taking evaluations

- Create TestPage with question navigation and timer
- Add QuizQuestion component supporting single/multiple/true_false types
- Add TestTimer component with color warnings
- Update courses store with startTest, fetchTest, submitTest actions
- Add route for /test/:id"
```

---

### Task 2: Create TestResultPage.vue

**Files:**
- Create: `resources/js/pages/courses/TestResultPage.vue`

**Step 1: Write the component**

```vue
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
```

**Step 2: Update courses store**

Add to `resources/js/stores/courses.ts`:

```typescript
async fetchTestResult(testId: number, attemptId: number): Promise<any> {
  this.loading = true
  try {
    const { data } = await api.get(`/tests/${testId}/result/${attemptId}`)
    return data.data
  } finally {
    this.loading = false
  }
}
```

**Step 3: Add route**

Modify `resources/js/router/index.ts`, add:

```typescript
{
  path: '/test/:id/result/:attemptId',
  component: () => import('@/pages/courses/TestResultPage.vue'),
  meta: { requiresAuth: true }
}
```

**Step 4: Commit**

```bash
git add resources/js/pages/courses/TestResultPage.vue resources/js/stores/courses.ts resources/js/router/index.ts
git commit -m "feat(courses): add TestResultPage.vue for viewing test results

- Display pass/fail status with visual indicators
- Show percentage score with progress bar
- Review mode with correct/incorrect answers
- Support for single/multiple/true_false question types
- Retry button for failed attempts
- Add fetchTestResult store action"
```

---

## PHASE 5: Public Frontend

### Task 3: Create PublicLayout.vue

**Files:**
- Create: `resources/js/layouts/PublicLayout.vue`

**Step 1: Write the layout**

```vue
<template>
  <div class="public-layout">
    <PublicHeader />
    <main class="public-main">
      <RouterView />
    </main>
    <PublicFooter />
  </div>
</template>

<script setup lang="ts">
import PublicHeader from '@/components/public/PublicHeader.vue'
import PublicFooter from '@/components/public/PublicFooter.vue'
</script>

<style scoped>
.public-layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.public-main {
  flex: 1;
}
</style>
```

**Step 2: Commit**

```bash
git add resources/js/layouts/PublicLayout.vue
git commit -m "feat(public): add PublicLayout component

- Base layout for public pages with header and footer"
```

---

### Task 4: Create PublicHeader.vue

**Files:**
- Create: `resources/js/components/public/PublicHeader.vue`

**Step 1: Write the component**

```vue
<template>
  <VAppBar
    :color="scrolled ? 'background' : 'transparent'"
    :elevation="scrolled ? 2 : 0"
    scroll-behavior="hide"
    class="public-header"
  >
    <VContainer class="d-flex align-center">
      <!-- Logo -->
      <RouterLink to="/" class="d-flex align-center text-decoration-none">
        <VImg
          v-if="branding?.logoLight"
          :src="branding.logoLight"
          height="40"
          width="120"
          contain
        />
        <span v-else class="text-h5 font-weight-bold">
          {{ tenantName }}
        </span>
      </RouterLink>

      <VSpacer />

      <!-- Desktop Nav -->
      <VBtn
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        variant="text"
        class="hidden-sm-and-down"
      >
        {{ item.label }}
      </VBtn>

      <VBtn
        to="/app"
        color="primary"
        variant="elevated"
        class="ml-4 hidden-sm-and-down"
      >
        Acceder
      </VBtn>

      <!-- Mobile Menu -->
      <VAppBarNavIcon
        class="hidden-md-and-up"
        @click="drawer = !drawer"
      />
    </VContainer>
  </VAppBar>

  <!-- Mobile Drawer -->
  <VNavigationDrawer
    v-model="drawer"
    location="right"
    temporary
  >
    <VList>
      <VListItem
        v-for="item in navItems"
        :key="item.path"
        :to="item.path"
        :title="item.label"
      />
      <VDivider class="my-2" />
      <VListItem to="/app" title="Acceder" />
    </VList>
  </VNavigationDrawer>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useWindowScroll } from '@vueuse/core'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()
const drawer = ref(false)
const { y } = useWindowScroll()

const scrolled = computed(() => y.value > 50)

const navItems = [
  { path: '/', label: 'Inicio' },
  { path: '/courses', label: 'Cursos' },
  { path: '/pricing', label: 'Precios' },
  { path: '/about', label: 'Nosotros' },
  { path: '/contact', label: 'Contacto' }
]

const branding = computed(() => publicStore.branding)
const tenantName = computed(() => publicStore.tenantName)

onMounted(() => {
  publicStore.fetchBranding()
})
</script>
```

**Step 2: Create public store**

Create `resources/js/stores/public.ts`:

```typescript
import { defineStore } from 'pinia'
import api from '@/api'

interface PublicState {
  branding: any | null
  tenantName: string
  loading: boolean
}

export const usePublicStore = defineStore('public', {
  state: (): PublicState => ({
    branding: null,
    tenantName: '',
    loading: false
  }),

  actions: {
    async fetchBranding() {
      this.loading = true
      try {
        const { data } = await api.get('/public/branding')
        this.branding = data.data.branding
        this.tenantName = data.data.tenant_name
      } finally {
        this.loading = false
      }
    }
  }
})
```

**Step 3: Commit**

```bash
git add resources/js/components/public/PublicHeader.vue resources/js/stores/public.ts
git commit -m "feat(public): add PublicHeader with mobile navigation

- Responsive header with logo and nav items
- Scroll behavior with transparent/solid transition
- Mobile drawer for small screens
- Create public store for branding data"
```

---

### Task 5: Create PublicFooter.vue

**Files:**
- Create: `resources/js/components/public/PublicFooter.vue`

**Step 1: Write the component**

```vue
<template>
  <VFooter class="public-footer" color="surface-variant">
    <VContainer>
      <VRow>
        <!-- Brand -->
        <VCol cols="12" md="4">
          <div class="d-flex align-center mb-4">
            <VImg
              v-if="branding?.logoLight"
              :src="branding.logoLight"
              height="32"
              width="100"
              contain
            />
            <span v-else class="text-h6 font-weight-bold">
              {{ tenantName }}
            </span>
          </div>
          <p class="text-body-2 text-grey">
            Formación profesional para impulsar tu carrera.
          </p>
        </VCol>

        <!-- Links -->
        <VCol cols="12" md="4">
          <h4 class="text-subtitle-1 font-weight-bold mb-4">Enlaces</h4>
          <VList density="compact" bg-color="transparent">
            <VListItem
              v-for="item in navItems"
              :key="item.path"
              :to="item.path"
              :title="item.label"
              density="compact"
            />
          </VList>
        </VCol>

        <!-- Contact -->
        <VCol cols="12" md="4">
          <h4 class="text-subtitle-1 font-weight-bold mb-4">Contacto</h4>
          <div v-if="branding?.contactInfo">
            <p v-if="branding.contactInfo.email" class="text-body-2">
              <VIcon icon="mdi-email" size="small" class="mr-2" />
              {{ branding.contactInfo.email }}
            </p>
            <p v-if="branding.contactInfo.phone" class="text-body-2 mt-2">
              <VIcon icon="mdi-phone" size="small" class="mr-2" />
              {{ branding.contactInfo.phone }}
            </p>
          </div>
          <div class="mt-4">
            <VBtn
              v-for="social in socialLinks"
              :key="social.icon"
              :href="social.url"
              target="_blank"
              icon
              variant="text"
              size="small"
            >
              <VIcon :icon="social.icon" />
            </VBtn>
          </div>
        </VCol>
      </VRow>

      <VDivider class="my-4" />

      <VRow align="center">
        <VCol cols="12" md="6" class="text-center text-md-left">
          <p class="text-caption text-grey">
            &copy; {{ currentYear }} {{ tenantName }}. Todos los derechos reservados.
          </p>
        </VCol>
        <VCol cols="12" md="6" class="text-center text-md-right">
          <VBtn
            to="/privacy"
            variant="text"
            size="small"
            density="compact"
          >
            Privacidad
          </VBtn>
          <VBtn
            to="/terms"
            variant="text"
            size="small"
            density="compact"
          >
            Términos
          </VBtn>
        </VCol>
      </VRow>
    </VContainer>
  </VFooter>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()

const currentYear = computed(() => new Date().getFullYear())
const branding = computed(() => publicStore.branding)
const tenantName = computed(() => publicStore.tenantName)

const navItems = [
  { path: '/', label: 'Inicio' },
  { path: '/courses', label: 'Cursos' },
  { path: '/pricing', label: 'Precios' },
  { path: '/about', label: 'Nosotros' }
]

const socialLinks = computed(() => {
  if (!branding.value?.socialLinks) return []
  return branding.value.socialLinks
})
</script>
```

**Step 2: Commit**

```bash
git add resources/js/components/public/PublicFooter.vue
git commit -m "feat(public): add PublicFooter component

- Brand section with logo
- Navigation links
- Contact information with social links
- Copyright and legal links"
```

---

### Task 6: Create HeroSection.vue

**Files:**
- Create: `resources/js/components/public/HeroSection.vue`

**Step 1: Write the component**

```vue
<template>
  <section class="hero-section" :style="sectionStyle">
    <VContainer>
      <VRow align="center" class="hero-content">
        <VCol cols="12" md="6">
          <h1 class="text-h2 font-weight-bold mb-4">
            {{ title }}
          </h1>
          <p class="text-h6 text-grey-lighten-1 mb-6">
            {{ subtitle }}
          </p>
          <div class="d-flex flex-wrap gap-2">
            <VBtn
              :to="primaryAction.to"
              color="primary"
              size="large"
              variant="elevated"
            >
              {{ primaryAction.label }}
            </VBtn>
            <VBtn
              v-if="secondaryAction"
              :to="secondaryAction.to"
              size="large"
              variant="outlined"
            >
              {{ secondaryAction.label }}
            </VBtn>
          </div>
        </VCol>
        <VCol cols="12" md="6" class="hidden-sm-and-down">
          <VImg
            v-if="image"
            :src="image"
            height="400"
            contain
          />
        </VCol>
      </VRow>
    </VContainer>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Action {
  label: string
  to: string
}

const props = withDefaults(defineProps<{
  title: string
  subtitle: string
  image?: string
  backgroundImage?: string
  primaryAction: Action
  secondaryAction?: Action
}>(), {
  image: undefined,
  backgroundImage: undefined
})

const sectionStyle = computed(() => {
  if (!props.backgroundImage) return {}
  return {
    backgroundImage: `url(${props.backgroundImage})`,
    backgroundSize: 'cover',
    backgroundPosition: 'center'
  }
})
</script>

<style scoped>
.hero-section {
  min-height: 80vh;
  display: flex;
  align-items: center;
}

.hero-content {
  min-height: 60vh;
}
</style>
```

**Step 2: Commit**

```bash
git add resources/js/components/public/HeroSection.vue
git commit -m "feat(public): add HeroSection component

- Configurable title, subtitle, and actions
- Support for background image
- Optional hero image for desktop
- Responsive layout"
```

---

### Task 7: Create FeatureCard.vue

**Files:**
- Create: `resources/js/components/public/FeatureCard.vue`

**Step 1: Write the component**

```vue
<template>
  <VCard
    class="feature-card h-100"
    :variant="variant"
  >
    <VCardText class="text-center pa-6">
      <VAvatar
        :color="iconColor"
        size="64"
        class="mb-4"
      >
        <VIcon :icon="icon" size="32" />
      </VAvatar>
      <h3 class="text-h6 font-weight-bold mb-2">{{ title }}</h3>
      <p class="text-body-2 text-grey">{{ description }}</p>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
defineProps<{
  icon: string
  iconColor?: string
  title: string
  description: string
  variant?: 'elevated' | 'flat' | 'tonal'
}>()
</script>

<style scoped>
.feature-card {
  transition: transform 0.2s;
}

.feature-card:hover {
  transform: translateY(-4px);
}
</style>
```

**Step 2: Commit**

```bash
git add resources/js/components/public/FeatureCard.vue
git commit -m "feat(public): add FeatureCard component

- Icon with customizable color
- Title and description
- Hover animation"
```

---

### Task 8: Create TestimonialCard.vue

**Files:**
- Create: `resources/js/components/public/TestimonialCard.vue`

**Step 1: Write the component**

```vue
<template>
  <VCard class="testimonial-card h-100">
    <VCardText class="pa-6">
      <div class="d-flex mb-4">
        <VIcon
          v-for="i in 5"
          :key="i"
          icon="mdi-star"
          :color="i <= rating ? 'warning' : 'grey-lighten-2'"
        />
      </div>

      <p class="text-body-1 mb-4">{{ text }}</p>

      <div class="d-flex align-center">
        <VAvatar
          v-if="avatar"
          :image="avatar"
          size="48"
          class="mr-3"
        />
        <VAvatar
          v-else
          color="primary"
          size="48"
          class="mr-3"
        >
          {{ initials }}
        </VAvatar>
        <div>
          <p class="font-weight-bold mb-0">{{ name }}</p>
          <p class="text-caption text-grey">{{ role }}</p>
        </div>
      </div>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  text: string
  name: string
  role: string
  rating: number
  avatar?: string
}>()

const initials = computed(() => {
  return props.name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})
</script>
```

**Step 2: Commit**

```bash
git add resources/js/components/public/TestimonialCard.vue
git commit -m "feat(public): add TestimonialCard component

- Star rating display
- Quote text
- Avatar with fallback initials
- Name and role"
```

---

### Task 9: Create ContactForm.vue

**Files:**
- Create: `resources/js/components/public/ContactForm.vue`

**Step 1: Write the component**

```vue
<template>
  <VForm ref="form" @submit.prevent="submit">
    <VRow>
      <VCol cols="12" md="6">
        <VTextField
          v-model="formData.name"
          label="Nombre"
          :rules="[requiredRule]"
          required
        />
      </VCol>
      <VCol cols="12" md="6">
        <VTextField
          v-model="formData.email"
          label="Email"
          type="email"
          :rules="[requiredRule, emailRule]"
          required
        />
      </VCol>
      <VCol cols="12">
        <VTextField
          v-model="formData.phone"
          label="Teléfono (opcional)"
          type="tel"
        />
      </VCol>
      <VCol cols="12">
        <VTextField
          v-model="formData.subject"
          label="Asunto"
          :rules="[requiredRule]"
          required
        />
      </VCol>
      <VCol cols="12">
        <VTextarea
          v-model="formData.message"
          label="Mensaje"
          rows="4"
          :rules="[requiredRule]"
          required
        />
      </VCol>
      <VCol cols="12">
        <VAlert
          v-if="success"
          type="success"
          class="mb-4"
        >
          Mensaje enviado correctamente. Te contactaremos pronto.
        </VAlert>
        <VAlert
          v-if="error"
          type="error"
          class="mb-4"
        >
          {{ error }}
        </VAlert>
        <VBtn
          type="submit"
          color="primary"
          size="large"
          :loading="loading"
          block
        >
          Enviar mensaje
        </VBtn>
      </VCol>
    </VRow>
  </VForm>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()

const form = ref<any>(null)
const loading = ref(false)
const success = ref(false)
const error = ref('')

const formData = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

const requiredRule = (v: string) => !!v || 'Campo requerido'
const emailRule = (v: string) => /.+@.+\..+/.test(v) || 'Email inválido'

async function submit() {
  const { valid } = await form.value?.validate()
  if (!valid) return

  loading.value = true
  error.value = ''
  success.value = false

  try {
    await publicStore.sendInquiry(formData)
    success.value = true
    form.value?.reset()
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Error al enviar mensaje'
  } finally {
    loading.value = false
  }
}
</script>
```

**Step 2: Update public store**

Add to `resources/js/stores/public.ts`:

```typescript
async sendInquiry(data: any) {
  await api.post('/public/inquiry', data)
}
```

**Step 3: Commit**

```bash
git add resources/js/components/public/ContactForm.vue resources/js/stores/public.ts
git commit -m "feat(public): add ContactForm component

- Name, email, phone, subject, message fields
- Validation rules
- Success/error states
- sendInquiry store action"
```

---

### Task 10: Create PricingCard.vue

**Files:**
- Create: `resources/js/components/public/PricingCard.vue`

**Step 1: Write the component**

```vue
<template>
  <VCard
    :class="{ 'featured-plan': featured }"
    :elevation="featured ? 8 : 2"
    class="pricing-card h-100"
  >
    <VCardText class="pa-6">
      <!-- Badge -->
      <VChip
        v-if="featured"
        color="primary"
        class="mb-4"
      >
        Más popular
      </VChip>

      <h3 class="text-h5 font-weight-bold">{{ plan.name }}</h3>
      <p class="text-body-2 text-grey mb-4">{{ plan.description }}</p>

      <!-- Price -->
      <div class="d-flex align-baseline mb-4">
        <span class="text-h3 font-weight-bold">
          ${{ plan.price }}
        </span>
        <span class="text-body-1 text-grey ml-2">
          / {{ durationLabel }}
        </span>
      </div>

      <!-- Features -->
      <VList density="compact" bg-color="transparent">
        <VListItem
          v-for="feature in plan.features"
          :key="feature"
          :title="feature"
          prepend-icon="mdi-check-circle"
        />
      </VList>

      <VBtn
        :to="`/checkout?plan=${plan.id}`"
        :color="featured ? 'primary' : 'secondary'"
        size="large"
        block
        class="mt-4"
      >
        Suscribirse
      </VBtn>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  plan: any
  featured?: boolean
}>()

const durationLabel = computed(() => {
  const days = props.plan.durationDays
  if (days >= 365) return `${days / 365} año${days >= 730 ? 's' : ''}`
  if (days >= 30) return `${Math.floor(days / 30)} mes${days >= 60 ? 'es' : ''}`
  return `${days} días`
})
</script>

<style scoped>
.pricing-card {
  transition: transform 0.2s;
}

.pricing-card:hover {
  transform: translateY(-4px);
}

.featured-plan {
  border: 2px solid rgb(var(--v-theme-primary));
}
</style>
```

**Step 2: Commit**

```bash
git add resources/js/components/public/PricingCard.vue
git commit -m "feat(public): add PricingCard component

- Plan name and description
- Price with duration label
- Feature list with checkmarks
- Featured state with styling
- Subscribe button linking to checkout"
```

---

### Task 11: Create HomePage.vue

**Files:**
- Create: `resources/js/pages/public/HomePage.vue`

**Step 1: Write the page**

```vue
<template>
  <div>
    <HeroSection
      title="Aprende sin límites"
      subtitle="Accede a cursos profesionales y mejora tus habilidades con expertos en la industria."
      primary-action="{ label: 'Ver cursos', to: '/courses' }"
      secondary-action="{ label: 'Saber más', to: '/about' }"
    />

    <!-- Features -->
    <section class="py-12 bg-surface">
      <VContainer>
        <VRow justify="center" class="mb-8">
          <VCol cols="12" md="8" class="text-center">
            <h2 class="text-h4 font-weight-bold">¿Por qué elegirnos?</h2>
            <p class="text-body-1 text-grey">Descubre lo que nos diferencia</p>
          </VCol>
        </VRow>

        <VRow>
          <VCol
            v-for="feature in features"
            :key="feature.title"
            cols="12"
            md="4"
          >
            <FeatureCard v-bind="feature" />
          </VCol>
        </VRow>
      </VContainer>
    </section>

    <!-- Featured Courses -->
    <section v-if="featuredCourses.length" class="py-12">
      <VContainer>
        <VRow justify="center" class="mb-8">
          <VCol cols="12" md="8" class="text-center">
            <h2 class="text-h4 font-weight-bold">Cursos destacados</h2>
            <p class="text-body-1 text-grey">Explora nuestros cursos más populares</p>
          </VCol>
        </VRow>

        <VRow>
          <VCol
            v-for="course in featuredCourses"
            :key="course.id"
            cols="12"
            md="4"
          >
            <CourseCard :course="course" />
          </VCol>
        </VRow>

        <VRow class="mt-6">
          <VCol class="text-center">
            <VBtn to="/courses" color="primary" size="large">
              Ver todos los cursos
            </VBtn>
          </VCol>
        </VRow>
      </VContainer>
    </section>

    <!-- Testimonials -->
    <section class="py-12 bg-surface">
      <VContainer>
        <VRow justify="center" class="mb-8">
          <VCol cols="12" md="8" class="text-center">
            <h2 class="text-h4 font-weight-bold">Lo que dicen nuestros estudiantes</h2>
          </VCol>
        </VRow>

        <VRow>
          <VCol
            v-for="testimonial in testimonials"
            :key="testimonial.name"
            cols="12"
            md="4"
          >
            <TestimonialCard v-bind="testimonial" />
          </VCol>
        </VRow>
      </VContainer>
    </section>

    <!-- CTA -->
    <section class="py-16">
      <VContainer>
        <VRow justify="center">
          <VCol cols="12" md="8" class="text-center">
            <h2 class="text-h3 font-weight-bold mb-4">
              ¿Listo para empezar?
            </h2>
            <p class="text-h6 text-grey mb-6">
              Únete a miles de estudiantes y transforma tu carrera hoy.
            </p>
            <VBtn
              to="/pricing"
              color="primary"
              size="x-large"
            >
              Ver planes y precios
            </VBtn>
          </VCol>
        </VRow>
      </VContainer>
    </section>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue'
import HeroSection from '@/components/public/HeroSection.vue'
import FeatureCard from '@/components/public/FeatureCard.vue'
import CourseCard from '@/components/courses/CourseCard.vue'
import TestimonialCard from '@/components/public/TestimonialCard.vue'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()

const featuredCourses = ref([])

const features = [
  {
    icon: 'mdi-school',
    iconColor: 'primary',
    title: 'Cursos de calidad',
    description: 'Contenido creado por expertos de la industria con años de experiencia.'
  },
  {
    icon: 'mdi-clock-fast',
    iconColor: 'success',
    title: 'Aprende a tu ritmo',
    description: 'Acceso ilimitado 24/7. Estudia cuando y donde quieras.'
  },
  {
    icon: 'mdi-certificate',
    iconColor: 'warning',
    title: 'Certificados',
    description: 'Obtén certificados al completar tus cursos y valida tus conocimientos.'
  }
]

const testimonials = [
  {
    text: 'Los cursos me ayudaron a cambiar de carrera. Ahora trabajo como desarrollador web gracias a lo aprendido aquí.',
    name: 'Juan Pérez',
    role: 'Desarrollador Web',
    rating: 5
  },
  {
    text: 'Excelente plataforma con instructores muy profesionales. El contenido es práctico y aplicable.',
    name: 'María García',
    role: 'Diseñadora UX',
    rating: 5
  },
  {
    text: 'La inversión más inteligente que hice en mi educación. Vale cada peso.',
    name: 'Carlos López',
    role: 'Project Manager',
    rating: 5
  }
]

onMounted(async () => {
  featuredCourses.value = await publicStore.fetchFeaturedCourses()
})
</script>
```

**Step 2: Update public store**

Add to `resources/js/stores/public.ts`:

```typescript
async fetchFeaturedCourses(): Promise<any[]> {
  try {
    const { data } = await api.get('/public/courses', {
      params: { featured: true, limit: 3 }
    })
    return data.data
  } catch {
    return []
  }
}
```

**Step 3: Commit**

```bash
git add resources/js/pages/public/HomePage.vue resources/js/stores/public.ts
git commit -m "feat(public): add HomePage with full content

- Hero section with CTAs
- Features section
- Featured courses carousel
- Testimonials section
- Call to action
- fetchFeaturedCourses store action"
```

---

### Task 12: Create PricingPage.vue

**Files:**
- Create: `resources/js/pages/public/PricingPage.vue`

**Step 1: Write the page**

```vue
<template>
  <div class="py-12">
    <VContainer>
      <!-- Header -->
      <VRow justify="center" class="mb-12">
        <VCol cols="12" md="8" class="text-center">
          <h1 class="text-h3 font-weight-bold mb-4">Planes y precios</h1>
          <p class="text-h6 text-grey">
            Elige el plan que mejor se adapte a tus necesidades
          </p>
        </VCol>
      </VRow>

      <!-- Loading -->
      <VRow v-if="loading" justify="center">
        <VCol cols="auto">
          <VProgressCircular indeterminate />
        </VCol>
      </VRow>

      <!-- Plans -->
      <VRow v-else>
        <VCol
          v-for="plan in plans"
          :key="plan.id"
          cols="12"
          md="4"
        >
          <PricingCard
            :plan="plan"
            :featured="plan.isFeatured"
          />
        </VCol>
      </VRow>

      <!-- FAQ -->
      <VRow justify="center" class="mt-16">
        <VCol cols="12" md="8">
          <h2 class="text-h4 font-weight-bold text-center mb-8">
            Preguntas frecuentes
          </h2>
          <VExpansionPanels>
            <VExpansionPanel
              v-for="faq in faqs"
              :key="faq.question"
            >
              <VExpansionPanelTitle>{{ faq.question }}</VExpansionPanelTitle>
              <VExpansionPanelText>{{ faq.answer }}</VExpansionPanelText>
            </VExpansionPanel>
          </VExpansionPanels>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import PricingCard from '@/components/public/PricingCard.vue'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()

const loading = ref(false)
const plans = ref([])

const faqs = [
  {
    question: '¿Puedo cambiar de plan?',
    answer: 'Sí, puedes cambiar de plan en cualquier momento. El nuevo precio se aplicará al próximo ciclo de facturación.'
  },
  {
    question: '¿Hay prueba gratuita?',
    answer: 'Algunos cursos ofrecen lecciones gratuitas de muestra para que puedas evaluar la calidad antes de suscribirte.'
  },
  {
    question: '¿Cómo cancelo mi suscripción?',
    answer: 'Puedes cancelar en cualquier momento desde tu panel de cuenta. Seguirás teniendo acceso hasta el final del período pagado.'
  }
]

onMounted(async () => {
  loading.value = true
  try {
    plans.value = await publicStore.fetchPlans()
  } finally {
    loading.value = false
  }
})
</script>
```

**Step 2: Update public store**

Add to `resources/js/stores/public.ts`:

```typescript
async fetchPlans(): Promise<any[]> {
  const { data } = await api.get('/public/plans')
  return data.data
}
```

**Step 3: Commit**

```bash
git add resources/js/pages/public/PricingPage.vue resources/js/stores/public.ts
git commit -m "feat(public): add PricingPage

- Plan grid with featured card
- FAQ section
- fetchPlans store action"
```

---

### Task 13: Create ContactPage.vue

**Files:**
- Create: `resources/js/pages/public/ContactPage.vue`

**Step 1: Write the page**

```vue
<template>
  <div class="py-12">
    <VContainer>
      <VRow justify="center">
        <VCol cols="12" md="8">
          <h1 class="text-h3 font-weight-bold text-center mb-4">Contacto</h1>
          <p class="text-h6 text-grey text-center mb-8">
            ¿Tienes preguntas? Estamos aquí para ayudarte.
          </p>
        </VCol>
      </VRow>

      <VRow>
        <VCol cols="12" md="6">
          <VCard>
            <VCardText class="pa-6">
              <h2 class="text-h5 font-weight-bold mb-4">Envíanos un mensaje</h2>
              <ContactForm />
            </VCardText>
          </VCard>
        </VCol>

        <VCol cols="12" md="6">
          <VCard class="h-100" color="primary" theme="dark">
            <VCardText class="pa-6">
              <h2 class="text-h5 font-weight-bold mb-6">Información de contacto</h2>

              <div class="mb-6">
                <VIcon icon="mdi-email" size="32" class="mb-2" />
                <p class="text-h6">Email</p>
                <p>{{ branding?.contactInfo?.email || 'contacto@ejemplo.com' }}</p>
              </div>

              <div class="mb-6">
                <VIcon icon="mdi-phone" size="32" class="mb-2" />
                <p class="text-h6">Teléfono</p>
                <p>{{ branding?.contactInfo?.phone || '+54 11 1234-5678' }}</p>
              </div>

              <div>
                <VIcon icon="mdi-map-marker" size="32" class="mb-2" />
                <p class="text-h6">Dirección</p>
                <p>{{ branding?.contactInfo?.address || 'Buenos Aires, Argentina' }}</p>
              </div>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import ContactForm from '@/components/public/ContactForm.vue'
import { usePublicStore } from '@/stores/public'

const publicStore = usePublicStore()
const branding = computed(() => publicStore.branding)
</script>
```

**Step 2: Commit**

```bash
git add resources/js/pages/public/ContactPage.vue
git commit -m "feat(public): add ContactPage

- Contact form on left
- Contact info card on right
- Uses branding from public store"
```

---

### Task 14: Create AboutPage.vue

**Files:**
- Create: `resources/js/pages/public/AboutPage.vue`

**Step 1: Write the page**

```vue
<template>
  <div class="py-12">
    <VContainer>
      <VRow justify="center" class="mb-12">
        <VCol cols="12" md="8" class="text-center">
          <h1 class="text-h3 font-weight-bold mb-4">Sobre nosotros</h1>
          <p class="text-h6 text-grey">
            Formando profesionales desde {{ foundingYear }}
          </p>
        </VCol>
      </VRow>

      <VRow align="center" class="mb-12">
        <VCol cols="12" md="6">
          <h2 class="text-h4 font-weight-bold mb-4">Nuestra misión</h2>
          <p class="text-body-1">
            Democratizar el acceso a la educación de calidad, permitiendo que cualquier persona
            pueda adquirir nuevas habilidades y transformar su vida profesional.
          </p>
        </VCol>
        <VCol cols="12" md="6">
          <VImg
            src="@images/about-mission.jpg"
            height="300"
            cover
            rounded="lg"
          />
        </VCol>
      </VRow>

      <VRow align="center" class="mb-12">
        <VCol cols="12" md="6" order="2" order-md="1">
          <VImg
            src="@images/about-vision.jpg"
            height="300"
            cover
            rounded="lg"
          />
        </VCol>
        <VCol cols="12" md="6" order="1" order-md="2">
          <h2 class="text-h4 font-weight-bold mb-4">Nuestra visión</h2>
          <p class="text-body-1">
            Ser la plataforma de referencia en educación online en Latinoamérica,
            reconocida por la calidad de nuestros cursos y el éxito de nuestros estudiantes.
          </p>
        </VCol>
      </VRow>

      <!-- Stats -->
      <VRow class="py-8 bg-surface rounded-lg mb-12">
        <VCol
          v-for="stat in stats"
          :key="stat.label"
          cols="6"
          md="3"
          class="text-center"
        >
          <p class="text-h3 font-weight-bold text-primary">{{ stat.value }}</p>
          <p class="text-body-1">{{ stat.label }}</p>
        </VCol>
      </VRow>

      <!-- Team -->
      <VRow>
        <VCol cols="12" class="text-center mb-8">
          <h2 class="text-h4 font-weight-bold">Nuestro equipo</h2>
        </VCol>
        <VCol
          v-for="member in team"
          :key="member.name"
          cols="12"
          sm="6"
          md="3"
          class="text-center"
        >
          <VAvatar
            :image="member.avatar"
            size="120"
            class="mb-4"
          />
          <h3 class="text-h6 font-weight-bold">{{ member.name }}</h3>
          <p class="text-body-2 text-grey">{{ member.role }}</p>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<script setup lang="ts">
const foundingYear = 2020

const stats = [
  { value: '10,000+', label: 'Estudiantes' },
  { value: '50+', label: 'Cursos' },
  { value: '20+', label: 'Instructores' },
  { value: '95%', label: 'Satisfacción' }
]

const team = [
  { name: 'Ana Rodríguez', role: 'Directora', avatar: '@images/team-1.jpg' },
  { name: 'Pedro Sánchez', role: 'CTO', avatar: '@images/team-2.jpg' },
  { name: 'Laura Martínez', role: 'Head of Education', avatar: '@images/team-3.jpg' },
  { name: 'Carlos Ruiz', role: 'Lead Instructor', avatar: '@images/team-4.jpg' }
]
</script>
```

**Step 2: Commit**

```bash
git add resources/js/pages/public/AboutPage.vue
git commit -m "feat(public): add AboutPage

- Mission and vision sections
- Stats display
- Team members grid"
```

---

### Task 15: Create PrivacyPage.vue

**Files:**
- Create: `resources/js/pages/public/PrivacyPage.vue`

**Step 1: Write the page**

```vue
<template>
  <div class="py-12">
    <VContainer>
      <VRow justify="center">
        <VCol cols="12" md="8">
          <h1 class="text-h3 font-weight-bold mb-6">Política de privacidad</h1>
          <p class="text-body-1 text-grey mb-8">
            Última actualización: {{ lastUpdated }}
          </p>

          <section
            v-for="section in sections"
            :key="section.title"
            class="mb-8"
          >
            <h2 class="text-h5 font-weight-bold mb-4">{{ section.title }}</h2>
            <p class="text-body-1">{{ section.content }}</p>
          </section>

          <VCard class="mt-8" color="surface-variant">
            <VCardText>
              <h3 class="text-h6 font-weight-bold mb-2">Contacto</h3>
              <p class="text-body-2">
                Si tienes preguntas sobre esta política de privacidad, contáctanos en
                <a :href="`mailto:${contactEmail}`">{{ contactEmail }}</a>
              </p>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<script setup lang="ts">
const lastUpdated = '23 de febrero de 2026'
const contactEmail = 'privacy@tenanta.com'

const sections = [
  {
    title: '1. Información que recopilamos',
    content: 'Recopilamos información personal que nos proporcionas directamente, como nombre, dirección de correo electrónico, número de teléfono y información de pago. También recopilamos automáticamente información sobre tu uso de nuestros servicios.'
  },
  {
    title: '2. Cómo utilizamos tu información',
    content: 'Utilizamos tu información para proporcionar, mantener y mejorar nuestros servicios, procesar transacciones, enviarte notificaciones y comunicaciones, y personalizar tu experiencia.'
  },
  {
    title: '3. Compartir información',
    content: 'No vendemos tu información personal. Solo compartimos información con proveedores de servicios que nos ayudan a operar nuestra plataforma, o cuando sea requerido por ley.'
  },
  {
    title: '4. Seguridad',
    content: 'Implementamos medidas de seguridad técnicas y organizativas para proteger tu información personal contra acceso no autorizado, alteración, divulgación o destrucción.'
  },
  {
    title: '5. Tus derechos',
    content: 'Tienes derecho a acceder, corregir o eliminar tu información personal. También puedes oponerte al procesamiento o solicitar la portabilidad de tus datos.'
  },
  {
    title: '6. Cambios a esta política',
    content: 'Podemos actualizar esta política periódicamente. Te notificaremos sobre cambios significativos mediante un aviso en nuestra plataforma o por correo electrónico.'
  }
]
</script>
```

**Step 2: Commit**

```bash
git add resources/js/pages/public/PrivacyPage.vue
git commit -m "feat(public): add PrivacyPage

- Privacy policy sections
- Contact information
- Last updated date"
```

---

### Task 16: Create TermsPage.vue

**Files:**
- Create: `resources/js/pages/public/TermsPage.vue`

**Step 1: Write the page**

```vue
<template>
  <div class="py-12">
    <VContainer>
      <VRow justify="center">
        <VCol cols="12" md="8">
          <h1 class="text-h3 font-weight-bold mb-6">Términos y condiciones</h1>
          <p class="text-body-1 text-grey mb-8">
            Última actualización: {{ lastUpdated }}
          </p>

          <section
            v-for="section in sections"
            :key="section.title"
            class="mb-8"
          >
            <h2 class="text-h5 font-weight-bold mb-4">{{ section.title }}</h2>
            <p class="text-body-1">{{ section.content }}</p>
          </section>

          <VCard class="mt-8" color="surface-variant">
            <VCardText>
              <h3 class="text-h6 font-weight-bold mb-2">Contacto</h3>
              <p class="text-body-2">
                Si tienes preguntas sobre estos términos, contáctanos en
                <a :href="`mailto:${contactEmail}`">{{ contactEmail }}</a>
              </p>
            </VCardText>
          </VCard>
        </VCol>
      </VRow>
    </VContainer>
  </div>
</template>

<script setup lang="ts">
const lastUpdated = '23 de febrero de 2026'
const contactEmail = 'legal@tenanta.com'

const sections = [
  {
    title: '1. Aceptación de los términos',
    content: 'Al acceder o utilizar nuestros servicios, aceptas estar sujeto a estos términos y condiciones. Si no estás de acuerdo con alguna parte de los términos, no podrás acceder a los servicios.'
  },
  {
    title: '2. Uso de los servicios',
    content: 'Nuestros servicios están destinados a fines educativos. No debes usar los servicios para ningún propósito ilegal o no autorizado. Eres responsable de mantener la confidencialidad de tu cuenta.'
  },
  {
    title: '3. Contenido y propiedad intelectual',
    content: 'Todo el contenido proporcionado a través de nuestros servicios está protegido por derechos de autor y otras leyes de propiedad intelectual. No puedes reproducir, distribuir o crear obras derivadas sin autorización.'
  },
  {
    title: '4. Pagos y suscripciones',
    content: 'Al adquirir una suscripción, aceptas pagar todas las tarifas aplicables. Las suscripciones se renuevan automáticamente a menos que se cancelen antes de la fecha de renovación. Los reembolsos están sujetos a nuestra política de reembolsos.'
  },
  {
    title: '5. Terminación',
    content: 'Podemos terminar o suspender tu acceso a los servicios inmediatamente, sin previo aviso, por cualquier motivo, incluyendo violación de estos términos.'
  },
  {
    title: '6. Limitación de responsabilidad',
    content: 'En ningún caso seremos responsables por daños indirectos, incidentales, especiales o consecuentes que surjan del uso o la imposibilidad de usar nuestros servicios.'
  },
  {
    title: '7. Cambios a los términos',
    content: 'Nos reservamos el derecho de modificar estos términos en cualquier momento. Los cambios entrarán en vigor inmediatamente después de su publicación.'
  },
  {
    title: '8. Ley aplicable',
    content: 'Estos términos se regirán e interpretarán de acuerdo con las leyes de la República Argentina, sin tener en cuenta sus disposiciones sobre conflicto de leyes.'
  }
]
</script>
```

**Step 2: Commit**

```bash
git add resources/js/pages/public/TermsPage.vue
git commit -m "feat(public): add TermsPage

- Terms and conditions sections
- Contact information
- Last updated date"
```

---

### Task 17: Create PublicController.php

**Files:**
- Create: `app/Http/Controllers/Api/PublicController.php`

**Step 1: Write the controller**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Courses\CourseResource;
use App\Http\Resources\Courses\SubscriptionPlanResource;
use App\Models\Courses\Course;
use App\Models\Courses\SubscriptionPlan;
use App\Services\BrandingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __construct(
        private readonly BrandingService $brandingService
    ) {}

    public function branding(): JsonResponse
    {
        $tenant = app('current_tenant');

        return response()->json([
            'data' => [
                'tenant_name' => $tenant?->name ?? config('app.name'),
                'branding' => $this->brandingService->getForTenant($tenant)
            ]
        ]);
    }

    public function courses(Request $request): JsonResponse
    {
        $query = Course::query()
            ->where('is_active', true)
            ->with(['blocks.topics'])
            ->orderBy('sort_order');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($request->has('limit')) {
            $query->limit($request->integer('limit'));
        }

        $courses = $query->get();

        return response()->json([
            'data' => CourseResource::collection($courses)
        ]);
    }

    public function course(string $slug): JsonResponse
    {
        $course = Course::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->with(['blocks.topics'])
            ->firstOrFail();

        return response()->json([
            'data' => new CourseResource($course)
        ]);
    }

    public function plans(): JsonResponse
    {
        $plans = SubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => SubscriptionPlanResource::collection($plans)
        ]);
    }
}
```

**Step 2: Create BrandingService**

Create `app/Services/BrandingService.php`:

```php
<?php

namespace App\Services;

use App\Models\Tenant;

class BrandingService
{
    public function getForTenant(?Tenant $tenant): array
    {
        if (!$tenant) {
            return config('branding.default', []);
        }

        return [
            'logoLight' => $tenant->logo_light,
            'logoDark' => $tenant->logo_dark,
            'favicon' => $tenant->favicon,
            'primaryColor' => $tenant->primary_color ?? '#696cff',
            'secondaryColor' => $tenant->secondary_color ?? '#8592a3',
            'socialLinks' => $tenant->social_links ?? [],
            'contactInfo' => $tenant->contact_info ?? [],
            'metaSeo' => $tenant->meta_seo ?? []
        ];
    }
}
```

**Step 3: Commit**

```bash
git add app/Http/Controllers/Api/PublicController.php app/Services/BrandingService.php
git commit -m "feat(public): add PublicController and BrandingService

- branding() endpoint for tenant info
- courses() and course() for public course listings
- plans() for subscription plans
- BrandingService to resolve tenant branding"
```

---

### Task 18: Create PublicInquiryController.php

**Files:**
- Create: `app/Http/Controllers/Api/PublicInquiryController.php`

**Step 1: Write the controller**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreInquiryRequest;
use App\Models\Inquiry;
use Illuminate\Http\JsonResponse;

class PublicInquiryController extends Controller
{
    public function store(StoreInquiryRequest $request): JsonResponse
    {
        $inquiry = Inquiry::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'phone' => $request->validated('phone'),
            'subject' => $request->validated('subject'),
            'message' => $request->validated('message'),
            'source' => 'contact_form',
            'status' => 'new'
        ]);

        // TODO: Send notification email to admin

        return response()->json([
            'message' => 'Consulta enviada correctamente',
            'data' => [
                'id' => $inquiry->id
            ]
        ], 201);
    }
}
```

**Step 2: Create StoreInquiryRequest**

Create `app/Http/Requests/Public/StoreInquiryRequest.php`:

```php
<?php

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000']
        ];
    }
}
```

**Step 3: Create Inquiry model and migration**

Run migration:

```bash
php artisan make:model Inquiry -m
```

Modify the generated migration `database/migrations/xxxx_create_inquiries_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->string('source')->default('contact_form');
            $table->enum('status', ['new', 'read', 'replied', 'archived'])->default('new');
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
```

Modify `app/Models/Inquiry.php`:

```php
<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'source',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];
}
```

**Step 4: Add routes**

Modify `routes/api.php`, add:

```php
// Public routes (no auth required)
Route::prefix('public')->group(function () {
    Route::get('/branding', [PublicController::class, 'branding']);
    Route::get('/courses', [PublicController::class, 'courses']);
    Route::get('/courses/{slug}', [PublicController::class, 'course']);
    Route::get('/plans', [PublicController::class, 'plans']);
    Route::post('/inquiry', [PublicInquiryController::class, 'store']);
});
```

**Step 5: Add public routes to Vue Router**

Modify `resources/js/router/index.ts`, add public routes:

```typescript
{
  path: '/',
  component: () => import('@/layouts/PublicLayout.vue'),
  children: [
    { path: '', component: () => import('@/pages/public/HomePage.vue') },
    { path: 'courses', component: () => import('@/pages/courses/CourseCatalogPage.vue') },
    { path: 'courses/:slug', component: () => import('@/pages/courses/CourseDetailPage.vue') },
    { path: 'pricing', component: () => import('@/pages/public/PricingPage.vue') },
    { path: 'contact', component: () => import('@/pages/public/ContactPage.vue') },
    { path: 'about', component: () => import('@/pages/public/AboutPage.vue') },
    { path: 'privacy', component: () => import('@/pages/public/PrivacyPage.vue') },
    { path: 'terms', component: () => import('@/pages/public/TermsPage.vue') }
  ]
}
```

**Step 6: Commit**

```bash
git add app/Http/Controllers/Api/PublicInquiryController.php app/Http/Requests/Public/StoreInquiryRequest.php app/Models/Inquiry.php database/migrations/xxxx_create_inquiries_table.php routes/api.php resources/js/router/index.ts
git commit -m "feat(public): add PublicInquiryController and Inquiry model

- Store inquiry endpoint with validation
- Create Inquiry model with BelongsToTenant trait
- Migration for inquiries table
- Add public routes to API and Vue router"
```

---

The plan continues with Phase 6 (Payment System), Phase 7 (Dual Versions), Phases 8 & 9 (Course Admin), Phase 10 (Integration), and testing.md documentation. Due to the length, I've included the critical initial tasks. Should I continue generating the remaining tasks?