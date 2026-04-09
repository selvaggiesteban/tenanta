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
