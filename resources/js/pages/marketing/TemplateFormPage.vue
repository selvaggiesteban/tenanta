<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'
import type { CreateTemplateData } from '@/types/marketing'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const store = useMarketingStore()

const isEditing = computed(() => !!route.params.id)
const templateId = computed(() => Number(route.params.id))

const form = ref<CreateTemplateData>({
  name: '',
  subject: '',
  content_html: '',
  content_text: '',
  type: 'marketing',
  category: '',
  variables: [],
  settings: {},
  is_active: true,
})

const previewDialog = ref(false)
const previewHtml = ref('')
const variablesInput = ref('')

const typeOptions = [
  { title: t('marketing.type.marketing'), value: 'marketing' },
  { title: t('marketing.type.transactional'), value: 'transactional' },
  { title: t('marketing.type.notification'), value: 'notification' },
]

const rules = {
  required: (v: any) => !!v || t('validation.required'),
}

async function loadTemplate() {
  if (isEditing.value) {
    await store.fetchTemplate(templateId.value)
    if (store.currentTemplate) {
      form.value = {
        name: store.currentTemplate.name,
        subject: store.currentTemplate.subject,
        content_html: store.currentTemplate.content_html,
        content_text: store.currentTemplate.content_text || '',
        type: store.currentTemplate.type,
        category: store.currentTemplate.category || '',
        variables: store.currentTemplate.variables || [],
        settings: store.currentTemplate.settings || {},
        is_active: store.currentTemplate.is_active,
      }
      variablesInput.value = (store.currentTemplate.variables || []).join(', ')
    }
  }
}

async function saveTemplate() {
  // Parse variables from input
  form.value.variables = variablesInput.value
    .split(',')
    .map(v => v.trim())
    .filter(v => v)

  try {
    if (isEditing.value) {
      await store.updateTemplate(templateId.value, form.value)
    } else {
      const template = await store.createTemplate(form.value)
      router.replace({ name: 'marketing-template-edit', params: { id: template.id } })
    }
  } catch (e) {
    // Error handled by store
  }
}

async function showPreview() {
  try {
    const result = await store.previewTemplate(templateId.value, {
      name: 'Usuario Ejemplo',
      email: 'usuario@ejemplo.com',
      first_name: 'Usuario',
    })
    previewHtml.value = result.html
    previewDialog.value = true
  } catch (e) {
    // Error handled by store
  }
}

function goBack() {
  router.push({ name: 'marketing-templates' })
}

onMounted(() => {
  store.fetchCategories()
  loadTemplate()
})
</script>

<template>
  <VContainer fluid>
    <VRow>
      <VCol cols="12">
        <VBtn
          variant="text"
          prepend-icon="mdi-arrow-left"
          @click="goBack"
        >
          {{ t('marketing.back_to_templates') }}
        </VBtn>
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle class="d-flex align-center">
            {{ isEditing ? t('marketing.edit_template') : t('marketing.new_template') }}
            <VSpacer />
            <VBtn
              v-if="isEditing"
              variant="outlined"
              prepend-icon="mdi-eye"
              @click="showPreview"
            >
              {{ t('marketing.preview') }}
            </VBtn>
          </VCardTitle>

          <VCardText>
            <VForm @submit.prevent="saveTemplate">
              <VRow>
                <VCol cols="12" md="6">
                  <VTextField
                    v-model="form.name"
                    :label="t('marketing.template_name')"
                    :rules="[rules.required]"
                    required
                  />
                </VCol>
                <VCol cols="12" md="3">
                  <VSelect
                    v-model="form.type"
                    :items="typeOptions"
                    :label="t('marketing.type')"
                  />
                </VCol>
                <VCol cols="12" md="3">
                  <VCombobox
                    v-model="form.category"
                    :items="store.categories"
                    :label="t('marketing.category')"
                    clearable
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="form.subject"
                    :label="t('marketing.subject')"
                    :rules="[rules.required]"
                    required
                    :hint="t('marketing.subject_hint')"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextField
                    v-model="variablesInput"
                    :label="t('marketing.variables')"
                    :hint="t('marketing.variables_hint')"
                    persistent-hint
                  />
                </VCol>
                <VCol cols="12">
                  <VTextarea
                    v-model="form.content_html"
                    :label="t('marketing.content_html')"
                    rows="15"
                    :rules="[rules.required]"
                    :hint="t('marketing.html_hint')"
                  />
                </VCol>
                <VCol cols="12">
                  <VTextarea
                    v-model="form.content_text"
                    :label="t('marketing.content_text')"
                    rows="5"
                    :hint="t('marketing.text_hint')"
                  />
                </VCol>
                <VCol cols="12">
                  <VSwitch
                    v-model="form.is_active"
                    :label="t('common.active')"
                    color="success"
                  />
                </VCol>
              </VRow>

              <div class="d-flex gap-2 mt-4">
                <VBtn type="submit" color="primary" :loading="store.loading">
                  {{ t('common.save') }}
                </VBtn>
                <VBtn variant="outlined" @click="goBack">
                  {{ t('common.cancel') }}
                </VBtn>
              </div>
            </VForm>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>

    <!-- Preview Dialog -->
    <VDialog v-model="previewDialog" max-width="800">
      <VCard>
        <VCardTitle>
          {{ t('marketing.preview') }}
          <VSpacer />
          <VBtn icon="mdi-close" variant="text" @click="previewDialog = false" />
        </VCardTitle>
        <VCardText>
          <iframe
            :srcdoc="previewHtml"
            style="width: 100%; height: 500px; border: 1px solid #ddd;"
          />
        </VCardText>
      </VCard>
    </VDialog>
  </VContainer>
</template>
