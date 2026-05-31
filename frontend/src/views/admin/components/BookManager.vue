<template>
  <div>
    <!-- Заголовок секции -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
          <i class="fas fa-shopping-bag text-[#c8a87c] text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-light text-[#2c2c2c]">Управление товарами</h3>
          <p class="text-xs text-[#8b7355] mt-0.5">Добавление, редактирование и экспорт товаров</p>
        </div>
      </div>
      <div class="flex gap-3">
        <button 
          @click="exportToExcel" 
          :disabled="exportLoading"
          class="px-4 py-2 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all flex items-center gap-2 disabled:opacity-50"
        >
          <i v-if="!exportLoading" class="fas fa-file-excel text-sm"></i>
          <i v-else class="fas fa-spinner fa-spin text-sm"></i>
          <span>Экспорт</span>
        </button>
        
        <button 
          @click="openModal()" 
          class="px-5 py-2 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all flex items-center gap-2 shadow-sm"
        >
          <i class="fas fa-plus text-xs"></i>
          Добавить товар
        </button>
      </div>
    </div>

    <!-- Фильтры -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <!-- Поиск -->
      <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#8b7355] text-sm"></i>
        <input 
          v-model="filters.search"
          type="text"
          placeholder="Поиск по названию..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
          @input="searchProducts"
        >
      </div>
      
      <!-- Бренд -->
      <select v-model="filters.author_id" class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer" @change="loadProducts">
        <option :value="null">Все бренды</option>
        <option v-for="author in authors" :key="author.id" :value="author.id">
          {{ author.name }}
        </option>
      </select>
      
      <!-- Категория -->
      <select 
        v-model="filters.genre_ids" 
        class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer"
        @change="loadProducts"
      >
        <option :value="[]">Все категории</option>
        <option v-for="genre in genres" :key="genre.id" :value="[genre.id]">
          {{ genre.name }}
        </option>
      </select>
      
      <!-- Статус -->
      <select v-model="filters.status" class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer" @change="loadProducts">
        <option :value="null">Все статусы</option>
        <option value="active">Активные</option>
        <option value="inactive">Неактивные</option>
      </select>
    </div>

    <!-- Таблица товаров -->
    <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-[#e8e0d8]">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b border-[#e8e0d8] bg-[#faf8f5]">
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Фото</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Название</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Бренд</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Категория</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Цена</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Остаток</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && products.length === 0">
            <td colspan="8" class="p-8 text-center">
              <div class="w-6 h-6 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p class="mt-3 text-[#8b7355] text-sm">Загрузка...</p>
            </td>
            </tr>
          <tr v-else-if="products.length === 0">
            <td colspan="8" class="p-8 text-center">
              <i class="fas fa-shopping-bag text-3xl text-[#e8e0d8] mb-2"></i>
              <p class="text-[#8b7355] text-sm">Товары не найдены</p>
            </td>
            </tr>
          <tr v-for="product in products" :key="product.id" class="border-b border-[#e8e0d8] hover:bg-[#faf8f5] transition-colors">
            <td class="p-4">
              <div class="w-12 h-14 rounded-lg bg-[#faf8f5] border border-[#e8e0d8] flex items-center justify-center overflow-hidden">
                <img 
                  v-if="product.cover_image && !imageErrors[product.id]" 
                  :src="getFullImageUrl(product.cover_image)" 
                  :alt="product.title"
                  class="w-full h-full object-cover"
                  @error="onImageError(product.id)"
                >
                <i v-else class="fas fa-shopping-bag text-[#c8a87c]/30 text-lg"></i>
              </div>
            </td>
            <td class="p-4">
              <span class="font-medium text-[#2c2c2c] text-sm" :title="product.title">
                {{ product.title }}
              </span>
            </td>
            <!-- ИСПРАВЛЕНО: brand вместо author -->
            <td class="p-4 text-[#8b7355] text-sm truncate max-w-[120px]" :title="product.brand?.name || '—'">
              {{ product.brand?.name || '—' }}
            </td>
            <!-- ИСПРАВЛЕНО: categories вместо genres -->
            <td class="p-4">
              <div class="flex flex-wrap gap-1">
                <span 
                  v-for="category in product.categories?.slice(0, 2)" 
                  :key="category.id"
                  class="px-1.5 py-0.5 bg-[#faf8f5] text-[#8b7355] rounded text-[10px]"
                >
                  {{ category.name }}
                </span>
                <span v-if="product.categories?.length > 2" class="text-[10px] text-[#8b7355]">
                  +{{ product.categories.length - 2 }}
                </span>
              </div>
            </td>
            <td class="p-4">
              <span class="font-medium text-[#2c2c2c] text-sm whitespace-nowrap">{{ formatPrice(product.price) }} ₽</span>
            </td>
            <td class="p-4">
              <span :class="product.is_in_stock ? 'text-green-600' : 'text-red-400'" class="text-sm">
                {{ product.is_in_stock ? (product.quantity || '—') : 'Нет' }}
              </span>
            </td>
            <td class="p-4">
              <div class="flex gap-2">
                <button 
                  @click="openModal(product)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] hover:text-[#c8a87c] transition-all"
                  title="Редактировать"
                >
                  <i class="fas fa-edit text-xs"></i>
                </button>
                <button 
                  @click="deleteProduct(product)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-red-400 hover:bg-red-50 hover:text-red-500 transition-all"
                  title="Удалить"
                >
                  <i class="fas fa-trash-alt text-xs"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Пагинация -->
      <div v-if="pagination.last_page > 1" class="flex justify-between items-center px-6 py-4 border-t border-[#e8e0d8]">
        <div class="text-xs text-[#8b7355]">
          {{ products.length }} из {{ pagination.total }}
        </div>
        <div class="flex gap-2">
          <button 
            @click="loadProducts(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="w-8 h-8 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <i class="fas fa-chevron-left text-xs"></i>
          </button>
          <span class="px-3 py-1.5 rounded-xl bg-[#c8a87c] text-white text-sm">
            {{ pagination.current_page }}
          </span>
          <button 
            @click="loadProducts(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="w-8 h-8 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <i class="fas fa-chevron-right text-xs"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Модальное окно -->
    <BookForm 
      v-if="showModal"
      :book="editingProduct"
      :authors="authors"
      :genres="genres"
      @close="closeModal"
      @saved="onProductSaved"
      @update-authors="loadAuthors"
      @update-genres="loadGenres"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { bookApi } from '@/api/books'
import { authorApi } from '@/api/authors'
import { genreApi } from '@/api/genres'
import { useToast } from '@/composables/useToast'
import BookForm from './BookForm.vue'

const { success, error } = useToast()

const products = ref([])
const authors = ref([])
const imageErrors = ref({})
const genres = ref([])
const showModal = ref(false)
const editingProduct = ref(null)
const exportLoading = ref(false)
const loading = ref(false)
const filters = ref({
  search: '',
  author_id: null,
  genre_ids: [],
  status: null
})
const searchTimeout = ref(null)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

const getFullImageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http://') || path.startsWith('https://')) {
    return path
  }
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  return `${baseUrl}${path}`
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0'
  return new Intl.NumberFormat('ru-RU').format(price)
}

const onImageError = (productId) => {
  imageErrors.value[productId] = true
}

const loadAuthors = async () => {
  try {
    const response = await authorApi.getAuthors()
    if (response.data.success) {
      authors.value = response.data.data
    }
  } catch (err) {
    console.error('Error loading brands:', err)
  }
}

const loadGenres = async () => {
  try {
    const response = await genreApi.getGenres()
    if (response.data.success) {
      genres.value = response.data.data
    }
  } catch (err) {
    console.error('Error loading categories:', err)
  }
}

const loadProducts = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: 20,
      search: filters.value.search,
      author_id: filters.value.author_id,
    }
    
    if (filters.value.genre_ids && filters.value.genre_ids.length > 0) {
      params.category_id = filters.value.genre_ids[0]
    }
    
    if (filters.value.status === 'active') params.is_active = true
    if (filters.value.status === 'inactive') params.is_active = false
    
    const response = await bookApi.getBooks(params)
    if (response.data.success) {
      products.value = response.data.data.data
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total
      }
    }
  } catch (err) {
    console.error('Error loading products:', err)
    error('Ошибка при загрузке товаров')
  } finally {
    loading.value = false
  }
}

const searchProducts = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    loadProducts()
  }, 500)
}

const openModal = (product = null) => {
  editingProduct.value = product
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  editingProduct.value = null
}

const onProductSaved = () => {
  loadProducts()
  closeModal()
}

const deleteProduct = async (product) => {
  if (confirm(`Удалить товар "${product.title}"?`)) {
    try {
      const response = await bookApi.deleteBook(product.id)
      if (response.data.success) {
        success('Товар удалён')
        await loadProducts()
      }
    } catch (err) {
      error(err.response?.data?.message || 'Ошибка при удалении')
    }
  }
}

const exportToExcel = async () => {
  exportLoading.value = true
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.author_id) params.author_id = filters.value.author_id
    if (filters.value.genre_ids && filters.value.genre_ids.length > 0) {
      params.category_id = filters.value.genre_ids[0]
    }
    if (filters.value.status === 'active') params.is_active = true
    if (filters.value.status === 'inactive') params.is_active = false
    
    const response = await bookApi.exportToExcel(params)
    const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `products-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    success('Экспорт выполнен')
  } catch (err) {
    error('Ошибка при экспорте')
  } finally {
    exportLoading.value = false
  }
}

onMounted(() => {
  loadAuthors()
  loadGenres()
  loadProducts()
})
</script>