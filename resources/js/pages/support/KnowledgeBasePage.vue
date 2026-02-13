<script setup lang="ts">
import { api } from '@/api'

interface Category {
  id: number
  name: string
  slug: string
  description: string | null
  icon: string | null
  articles_count: number
}

interface Article {
  id: number
  title: string
  slug: string
  excerpt: string | null
  content?: string
  category?: { id: number; name: string }
  author: { id: number; name: string }
  views: number
  reading_time: number
  published_at: string | null
}

const categories = ref<Category[]>([])
const articles = ref<Article[]>([])
const selectedCategory = ref<number | null>(null)
const selectedArticle = ref<Article | null>(null)
const loading = ref(false)
const search = ref('')
const articleDialog = ref(false)

onMounted(() => {
  loadCategories()
  loadArticles()
})

const loadCategories = async () => {
  try {
    const response = await api.get('/support/kb/categories?root_only=true')
    categories.value = response.data.data
  } catch (error) {
    console.error('Error loading categories:', error)
  }
}

const loadArticles = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (selectedCategory.value) params.append('category_id', String(selectedCategory.value))
    if (search.value) params.append('search', search.value)

    const response = await api.get(`/support/kb/articles?${params}`)
    articles.value = response.data.data
  } catch (error) {
    console.error('Error loading articles:', error)
  } finally {
    loading.value = false
  }
}

const viewArticle = async (article: Article) => {
  try {
    const response = await api.get(`/support/kb/articles/${article.id}`)
    selectedArticle.value = response.data.data
    articleDialog.value = true
  } catch (error) {
    console.error('Error loading article:', error)
  }
}

const sendFeedback = async (helpful: boolean) => {
  if (!selectedArticle.value) return
  try {
    await api.post(`/support/kb/articles/${selectedArticle.value.id}/feedback`, { helpful })
  } catch (error) {
    console.error('Error sending feedback:', error)
  }
}

const selectCategory = (categoryId: number | null) => {
  selectedCategory.value = categoryId
  loadArticles()
}

watch(search, () => loadArticles())
</script>

<template>
  <div>
    <div class="mb-6">
      <h4 class="text-h4 font-weight-bold">Base de Conocimientos</h4>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Encuentra respuestas a preguntas frecuentes
      </p>
    </div>

    <!-- Search -->
    <VCard class="mb-6">
      <VCardText>
        <VTextField
          v-model="search"
          label="Buscar artículos..."
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          hide-details
          clearable
        />
      </VCardText>
    </VCard>

    <VRow>
      <!-- Categories Sidebar -->
      <VCol cols="12" md="3">
        <VCard>
          <VCardTitle>Categorías</VCardTitle>
          <VList density="compact" nav>
            <VListItem
              :active="selectedCategory === null"
              @click="selectCategory(null)"
            >
              <template #prepend>
                <VIcon icon="mdi-folder-multiple" />
              </template>
              <VListItemTitle>Todas</VListItemTitle>
            </VListItem>
            <VListItem
              v-for="cat in categories"
              :key="cat.id"
              :active="selectedCategory === cat.id"
              @click="selectCategory(cat.id)"
            >
              <template #prepend>
                <VIcon :icon="cat.icon || 'mdi-folder'" />
              </template>
              <VListItemTitle>{{ cat.name }}</VListItemTitle>
              <template #append>
                <VChip size="x-small">{{ cat.articles_count }}</VChip>
              </template>
            </VListItem>
          </VList>
        </VCard>
      </VCol>

      <!-- Articles -->
      <VCol cols="12" md="9">
        <VCard :loading="loading">
          <VCardText v-if="articles.length === 0 && !loading">
            <div class="text-center py-8 text-medium-emphasis">
              <VIcon icon="mdi-file-document-outline" size="64" class="mb-4" />
              <p>No se encontraron artículos</p>
            </div>
          </VCardText>
          <VList v-else>
            <VListItem
              v-for="article in articles"
              :key="article.id"
              @click="viewArticle(article)"
            >
              <template #prepend>
                <VIcon icon="mdi-file-document-outline" />
              </template>
              <VListItemTitle>{{ article.title }}</VListItemTitle>
              <VListItemSubtitle>
                {{ article.excerpt || 'Sin descripción' }}
              </VListItemSubtitle>
              <template #append>
                <div class="text-caption text-medium-emphasis text-right">
                  <div>{{ article.reading_time }} min lectura</div>
                  <div>{{ article.views }} vistas</div>
                </div>
              </template>
            </VListItem>
          </VList>
        </VCard>
      </VCol>
    </VRow>

    <!-- Article Dialog -->
    <VDialog v-model="articleDialog" max-width="800" scrollable>
      <VCard v-if="selectedArticle">
        <VCardTitle>
          <div>
            <VChip v-if="selectedArticle.category" size="small" class="me-2">
              {{ selectedArticle.category.name }}
            </VChip>
            {{ selectedArticle.title }}
          </div>
        </VCardTitle>
        <VCardSubtitle>
          Por {{ selectedArticle.author.name }} |
          {{ selectedArticle.reading_time }} min lectura |
          {{ selectedArticle.views }} vistas
        </VCardSubtitle>
        <VDivider />
        <VCardText style="max-height: 60vh; overflow-y: auto;">
          <div v-html="selectedArticle.content" class="article-content" />
        </VCardText>
        <VDivider />
        <VCardText>
          <div class="d-flex align-center justify-center">
            <span class="me-4">¿Te fue útil este artículo?</span>
            <VBtn variant="outlined" color="success" class="me-2" @click="sendFeedback(true)">
              <VIcon icon="mdi-thumb-up" class="me-1" />
              Sí
            </VBtn>
            <VBtn variant="outlined" color="error" @click="sendFeedback(false)">
              <VIcon icon="mdi-thumb-down" class="me-1" />
              No
            </VBtn>
          </div>
        </VCardText>
        <VCardActions>
          <VSpacer />
          <VBtn variant="text" @click="articleDialog = false">Cerrar</VBtn>
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<style scoped>
.article-content :deep(h1),
.article-content :deep(h2),
.article-content :deep(h3) {
  margin-top: 1.5rem;
  margin-bottom: 0.5rem;
}

.article-content :deep(p) {
  margin-bottom: 1rem;
}

.article-content :deep(ul),
.article-content :deep(ol) {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
}

.article-content :deep(code) {
  background: rgba(0, 0, 0, 0.05);
  padding: 0.2rem 0.4rem;
  border-radius: 4px;
}

.article-content :deep(pre) {
  background: rgba(0, 0, 0, 0.05);
  padding: 1rem;
  border-radius: 8px;
  overflow-x: auto;
}
</style>
