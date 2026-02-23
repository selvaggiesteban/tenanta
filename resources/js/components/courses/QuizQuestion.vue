<template>
  <VCard variant="outlined" class="mb-4">
    <VCardTitle class="d-flex align-start pa-4">
      <span class="me-3 text-primary font-weight-bold">
        {{ questionNumber }}.
      </span>
      <span class="text-body-1">{{ question.question }}</span>
    </VCardTitle>

    <VCardText v-if="question.explanation" class="pt-0 pb-2">
      <p class="text-body-2 text-medium-emphasis">{{ question.explanation }}</p>
    </VCardText>

    <VCardText class="pt-0">
      <!-- Single Choice -->
      <VRadioGroup
        v-if="isSingleChoice"
        v-model="singleValue"
        hide-details
        density="comfortable"
      >
        <VRadio
          v-for="option in question.options"
          :key="option.id"
          :label="option.text"
          :value="option.id"
          class="mb-2"
        />
      </VRadioGroup>

      <!-- Multiple Choice -->
      <div v-else-if="isMultipleChoice" class="d-flex flex-column gap-2">
        <VCheckbox
          v-for="option in question.options"
          :key="option.id"
          v-model="multipleValue"
          :label="option.text"
          :value="option.id"
          hide-details
          density="comfortable"
        />
      </div>

      <!-- True/False -->
      <VRadioGroup
        v-else-if="isTrueFalse"
        v-model="trueFalseValue"
        hide-details
        density="comfortable"
      >
        <VRadio
          v-for="option in trueFalseOptions"
          :key="option.id"
          :label="option.text"
          :value="option.id"
          class="mb-2"
        />
      </VRadioGroup>
    </VCardText>

    <VCardText v-if="question.points" class="pt-0">
      <VChip size="small" color="info" variant="tonal">
        {{ question.points }} punto{{ question.points !== 1 ? 's' : '' }}
      </VChip>
    </VCardText>
  </VCard>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { PropType } from 'vue'
import type { AttemptQuestion } from '@/types/courses'

const props = defineProps({
  question: {
    type: Object as PropType<AttemptQuestion>,
    required: true,
  },
  modelValue: {
    type: Array as PropType<number[]>,
    default: () => [],
  },
  questionNumber: {
    type: Number,
    required: true,
  },
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: number[]): void
}>()

const isSingleChoice = computed(() => props.question.type === 'single')
const isMultipleChoice = computed(() => props.question.type === 'multiple')
const isTrueFalse = computed(() => props.question.type === 'true_false')

const singleValue = computed({
  get: () => props.modelValue[0] ?? null,
  set: (val: number | null) => {
    emit('update:modelValue', val !== null ? [val] : [])
  },
})

const multipleValue = computed({
  get: () => props.modelValue,
  set: (val: number[]) => {
    emit('update:modelValue', val)
  },
})

const trueFalseOptions = [
  { id: 1, text: 'Verdadero' },
  { id: 0, text: 'Falso' },
]

const trueFalseValue = computed({
  get: () => {
    if (props.modelValue.length === 0) return null
    return props.modelValue[0]
  },
  set: (val: number | null) => {
    emit('update:modelValue', val !== null ? [val] : [])
  },
})
</script>
