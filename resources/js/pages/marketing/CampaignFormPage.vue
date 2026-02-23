<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketingStore } from '@/stores/marketing'
import { useI18n } from 'vue-i18n'
import type { CreateCampaignData, AddRecipientsData } from '@/types/marketing'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const store = useMarketingStore()

const isEditing = computed(() => !!route.params.id)
const campaignId = computed(() => Number(route.params.id))
const activeTab = ref('content')

const form = ref<CreateCampaignData>({
  name: '',
  subject: '',
  from_name: '',
  from_email: '',
  reply_to: '',
  template_id: undefined,
  content_html: '',
  content_text: '',
  type: 'regular',
  settings: {},
})

const recipientSource = ref<'list' | 'emails'>('list')
const selectedListId = ref<number | null>(null)
const manualEmails = ref('')

const scheduleDate = ref('')
const scheduleTime = ref('')

const rules = {
  required: (v: any) => !!v || t('validation.required'),
  email: (v: string) => !v || /.+@.+\..+/.test(v) || t('validation.email'),
}

async function loadCampaign() {
  if (isEditing.value) {
    await store.fetchCampaign(campaignId.value)
    if (store.currentCampaign) {
      form.value = {
        name: store.currentCampaign.name,
        subject: store.currentCampaign.subject,
        from_name: store.currentCampaign.from_name,
        from_email: store.currentCampaign.from_email,
        reply_to: store.currentCampaign.reply_to || '',
        template_id: store.currentCampaign.template_id || undefined,
        content_html: store.currentCampaign.content_html || '',
        content_text: store.currentCampaign.content_text || '',
        type: store.currentCampaign.type,
        settings: store.currentCampaign.settings,
      }
      if (store.currentCampaign.scheduled_at) {
        const scheduledDate = new Date(store.currentCampaign.scheduled_at)
        scheduleDate.value = scheduledDate.toISOString().split('T')[0]
        scheduleTime.value = scheduledDate.toTimeString().slice(0, 5)
      }
    }
  }
}

async function saveCampaign() {
  try {
    if (isEditing.value) {
      await store.updateCampaign(campaignId.value, form.value)
    } else {
      const campaign = await store.createCampaign(form.value)
      router.replace({ name: 'marketing-campaign-edit', params: { id: campaign.id } })
    }
  } catch (e) {
    // Error handled by store
  }
}

async function addRecipients() {
  if (!isEditing.value) return

  const data: AddRecipientsData = {
    source: recipientSource.value,
  }

  if (recipientSource.value === 'list' && selectedListId.value) {
    data.list_id = selectedListId.value
  } else if (recipientSource.value === 'emails') {
    const emails = manualEmails.value
      .split('\n')
      .map(e => e.trim())
      .filter(e => e)
      .map(e => ({ email: e }))
    data.emails = emails
  }

  try {
    await store.addCampaignRecipients(campaignId.value, data)
    await store.fetchCampaign(campaignId.value)
    manualEmails.value = ''
  } catch (e) {
    // Error handled by store
  }
}

async function scheduleCampaign() {
  if (!scheduleDate.value || !scheduleTime.value) return

  const scheduledAt = `${scheduleDate.value}T${scheduleTime.value}:00`
  try {
    await store.scheduleCampaign(campaignId.value, scheduledAt)
    await store.fetchCampaign(campaignId.value)
  } catch (e) {
    // Error handled by store
  }
}

async function cancelSchedule() {
  try {
    await store.cancelSchedule(campaignId.value)
    await store.fetchCampaign(campaignId.value)
    scheduleDate.value = ''
    scheduleTime.value = ''
  } catch (e) {
    // Error handled by store
  }
}

async function sendNow() {
  try {
    await store.sendCampaign(campaignId.value)
    router.push({ name: 'marketing-campaign-stats', params: { id: campaignId.value } })
  } catch (e) {
    // Error handled by store
  }
}

function goBack() {
  router.push({ name: 'marketing-campaigns' })
}

// Load template content when selected
watch(() => form.value.template_id, async (templateId) => {
  if (templateId && !form.value.content_html) {
    await store.fetchTemplate(templateId)
    if (store.currentTemplate) {
      form.value.content_html = store.currentTemplate.content_html
      form.value.content_text = store.currentTemplate.content_text || ''
      if (!form.value.subject) {
        form.value.subject = store.currentTemplate.subject
      }
    }
  }
})

onMounted(async () => {
  await store.fetchTemplates({ active_only: true })
  await store.fetchLists({ active_only: true })
  await loadCampaign()
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
          {{ t('marketing.back_to_campaigns') }}
        </VBtn>
      </VCol>
    </VRow>

    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardTitle>
            {{ isEditing ? t('marketing.edit_campaign') : t('marketing.new_campaign') }}
          </VCardTitle>

          <VCardText>
            <VTabs v-model="activeTab">
              <VTab value="content">{{ t('marketing.content') }}</VTab>
              <VTab value="recipients" :disabled="!isEditing">
                {{ t('marketing.recipients') }}
                <VChip v-if="store.currentCampaign" size="x-small" class="ml-2">
                  {{ store.currentCampaign.stats.recipient_count }}
                </VChip>
              </VTab>
              <VTab value="schedule" :disabled="!isEditing">{{ t('marketing.schedule') }}</VTab>
            </VTabs>

            <VWindow v-model="activeTab" class="mt-4">
              <!-- Content Tab -->
              <VWindowItem value="content">
                <VForm @submit.prevent="saveCampaign">
                  <VRow>
                    <VCol cols="12" md="6">
                      <VTextField
                        v-model="form.name"
                        :label="t('marketing.campaign_name')"
                        :rules="[rules.required]"
                        required
                      />
                    </VCol>
                    <VCol cols="12" md="6">
                      <VSelect
                        v-model="form.template_id"
                        :items="store.activeTemplates"
                        item-title="name"
                        item-value="id"
                        :label="t('marketing.template')"
                        clearable
                      />
                    </VCol>
                    <VCol cols="12">
                      <VTextField
                        v-model="form.subject"
                        :label="t('marketing.subject')"
                        :rules="[rules.required]"
                        required
                      />
                    </VCol>
                    <VCol cols="12" md="4">
                      <VTextField
                        v-model="form.from_name"
                        :label="t('marketing.from_name')"
                        :rules="[rules.required]"
                        required
                      />
                    </VCol>
                    <VCol cols="12" md="4">
                      <VTextField
                        v-model="form.from_email"
                        :label="t('marketing.from_email')"
                        :rules="[rules.required, rules.email]"
                        type="email"
                        required
                      />
                    </VCol>
                    <VCol cols="12" md="4">
                      <VTextField
                        v-model="form.reply_to"
                        :label="t('marketing.reply_to')"
                        :rules="[rules.email]"
                        type="email"
                      />
                    </VCol>
                    <VCol cols="12">
                      <VTextarea
                        v-model="form.content_html"
                        :label="t('marketing.content_html')"
                        rows="10"
                        :rules="[rules.required]"
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
              </VWindowItem>

              <!-- Recipients Tab -->
              <VWindowItem value="recipients">
                <VRow>
                  <VCol cols="12" md="6">
                    <VCard variant="outlined">
                      <VCardTitle class="text-subtitle-1">
                        {{ t('marketing.add_recipients') }}
                      </VCardTitle>
                      <VCardText>
                        <VRadioGroup v-model="recipientSource" inline>
                          <VRadio :label="t('marketing.from_list')" value="list" />
                          <VRadio :label="t('marketing.manual_emails')" value="emails" />
                        </VRadioGroup>

                        <VSelect
                          v-if="recipientSource === 'list'"
                          v-model="selectedListId"
                          :items="store.activeLists"
                          item-title="name"
                          item-value="id"
                          :label="t('marketing.select_list')"
                          class="mt-2"
                        >
                          <template #item="{ item, props }">
                            <VListItem v-bind="props">
                              <template #append>
                                <VChip size="x-small">
                                  {{ item.raw.active_count }} {{ t('marketing.active') }}
                                </VChip>
                              </template>
                            </VListItem>
                          </template>
                        </VSelect>

                        <VTextarea
                          v-if="recipientSource === 'emails'"
                          v-model="manualEmails"
                          :label="t('marketing.emails_one_per_line')"
                          rows="5"
                          class="mt-2"
                        />

                        <VBtn
                          color="primary"
                          class="mt-4"
                          :disabled="(recipientSource === 'list' && !selectedListId) || (recipientSource === 'emails' && !manualEmails)"
                          :loading="store.loading"
                          @click="addRecipients"
                        >
                          {{ t('marketing.add_recipients') }}
                        </VBtn>
                      </VCardText>
                    </VCard>
                  </VCol>

                  <VCol cols="12" md="6">
                    <VCard variant="outlined">
                      <VCardTitle class="text-subtitle-1">
                        {{ t('marketing.current_recipients') }}
                      </VCardTitle>
                      <VCardText>
                        <div v-if="store.currentCampaign" class="text-h4 text-center my-4">
                          {{ store.currentCampaign.stats.recipient_count.toLocaleString() }}
                        </div>
                        <p class="text-center text-medium-emphasis">
                          {{ t('marketing.recipients_will_receive') }}
                        </p>
                      </VCardText>
                    </VCard>
                  </VCol>
                </VRow>
              </VWindowItem>

              <!-- Schedule Tab -->
              <VWindowItem value="schedule">
                <VRow>
                  <VCol cols="12" md="6">
                    <VCard variant="outlined">
                      <VCardTitle class="text-subtitle-1">
                        {{ t('marketing.schedule_campaign') }}
                      </VCardTitle>
                      <VCardText>
                        <VRow>
                          <VCol cols="6">
                            <VTextField
                              v-model="scheduleDate"
                              :label="t('marketing.date')"
                              type="date"
                            />
                          </VCol>
                          <VCol cols="6">
                            <VTextField
                              v-model="scheduleTime"
                              :label="t('marketing.time')"
                              type="time"
                            />
                          </VCol>
                        </VRow>

                        <div class="d-flex gap-2 mt-4">
                          <VBtn
                            color="primary"
                            :disabled="!scheduleDate || !scheduleTime"
                            :loading="store.loading"
                            @click="scheduleCampaign"
                          >
                            {{ t('marketing.schedule') }}
                          </VBtn>
                          <VBtn
                            v-if="store.currentCampaign?.status === 'scheduled'"
                            color="warning"
                            variant="outlined"
                            @click="cancelSchedule"
                          >
                            {{ t('marketing.cancel_schedule') }}
                          </VBtn>
                        </div>
                      </VCardText>
                    </VCard>
                  </VCol>

                  <VCol cols="12" md="6">
                    <VCard variant="outlined">
                      <VCardTitle class="text-subtitle-1">
                        {{ t('marketing.send_now') }}
                      </VCardTitle>
                      <VCardText>
                        <VAlert
                          v-if="store.currentCampaign?.stats.recipient_count === 0"
                          type="warning"
                          class="mb-4"
                        >
                          {{ t('marketing.no_recipients_warning') }}
                        </VAlert>

                        <p class="mb-4">
                          {{ t('marketing.send_now_description') }}
                        </p>

                        <VBtn
                          color="success"
                          :disabled="!store.currentCampaign || store.currentCampaign.stats.recipient_count === 0"
                          :loading="store.loading"
                          @click="sendNow"
                        >
                          <VIcon icon="mdi-send" class="mr-2" />
                          {{ t('marketing.send_now') }}
                        </VBtn>
                      </VCardText>
                    </VCard>
                  </VCol>
                </VRow>
              </VWindowItem>
            </VWindow>
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </VContainer>
</template>
