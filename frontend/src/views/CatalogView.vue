<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Hero секция каталога -->
    <div class="mb-12 text-center">
      <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-3 font-light">Collection</p>
      <h1 class="text-3xl sm:text-4xl font-light text-black tracking-tight">Каталог</h1>
      <div class="w-12 h-px bg-black/20 mx-auto mt-4"></div>
    </div>

    <!-- Фильтры -->
    <div class="mb-10">
      <!-- Поиск -->
      <div class="mb-8">
        <div class="relative max-w-md mx-auto">
          <i class="fas fa-search absolute left-0 top-1/2 -translate-y-1/2 text-black/20 text-sm"></i>
          <input 
            type="text" 
            v-model="filters.search"
            placeholder="Поиск по названию или бренду..." 
            class="w-full pl-7 pr-4 py-3 border-b border-black/10 bg-transparent focus:outline-none focus:border-black/30 transition-all text-sm font-light"
            @keyup.enter="applyFilters"
          >
        </div>
      </div>

      <!-- Фильтры - горизонтальный скролл на мобильных -->
      <div class="border-b border-black/5 pb-4">
        <div class="flex items-center justify-between mb-4">
          <button 
            @click="showAdvancedFilters = !showAdvancedFilters"
            class="flex items-center gap-2 text-xs tracking-[0.2em] uppercase text-black/40 hover:text-black transition-colors font-light"
          >
            <i :class="showAdvancedFilters ? 'fa-minus' : 'fa-plus'" class="fas text-[10px]"></i>
            <span>Фильтры</span>
            <span v-if="activeFiltersCount > 0" class="bg-black/10 text-black text-[9px] px-1.5 py-0.5 rounded-full ml-1">
              {{ activeFiltersCount }}
            </span>
          </button>
          
          <button 
            @click="resetFilters"
            class="text-[10px] tracking-[0.2em] uppercase text-black/30 hover:text-black/60 transition-colors font-light"
          >
            Сбросить все
          </button>
        </div>

        <div v-show="showAdvancedFilters" class="space-y-6 pt-2">
          <!-- Активные фильтры (чипы) -->
          <div v-if="activeFiltersCount > 0" class="flex flex-wrap gap-2 pb-2 border-b border-black/5">
            <span v-if="filters.search" class="inline-flex items-center gap-1.5 text-[10px] bg-black/5 px-2 py-1">
              Поиск: {{ filters.search }}
              <button @click="filters.search = ''; applyFilters()" class="ml-1">✕</button>
            </span>
            <span v-if="filters.category_id" class="inline-flex items-center gap-1.5 text-[10px] bg-black/5 px-2 py-1">
              {{ getCategoryName(filters.category_id) }}
              <button @click="filters.category_id = ''; applyFilters()" class="ml-1">✕</button>
            </span>
            <span v-if="filters.price_from || filters.price_to" class="inline-flex items-center gap-1.5 text-[10px] bg-black/5 px-2 py-1">
              Цена: {{ filters.price_from || '0' }} - {{ filters.price_to || '∞' }} ₽
              <button @click="filters.price_from = ''; filters.price_to = ''; applyFilters()" class="ml-1">✕</button>
            </span>
            <span v-if="filters.in_stock" class="inline-flex items-center gap-1.5 text-[10px] bg-black/5 px-2 py-1">
              В наличии
              <button @click="filters.in_stock = false; applyFilters()" class="ml-1">✕</button>
            </span>
          </div>

          <!-- Сетка фильтров -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Категория -->
            <div>
              <label class="block text-[10px] tracking-[0.2em] uppercase text-black/40 mb-2 font-light">Категория</label>
              <select 
                v-model="filters.category_id"
                class="w-full px-0 py-2 text-sm border-b border-black/10 bg-transparent focus:outline-none focus:border-black/30 transition-all"
                @change="applyFilters"
              >
                <option value="">Все категории</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Цена -->
            <div>
              <label class="block text-[10px] tracking-[0.2em] uppercase text-black/40 mb-2 font-light">Цена (₽)</label>
              <div class="flex gap-2">
                <input 
                  type="number" 
                  v-model="filters.price_from"
                  placeholder="от"
                  class="w-1/2 px-0 py-2 text-sm border-b border-black/10 bg-transparent focus:outline-none focus:border-black/30 transition-all"
                  @change="applyFilters"
                >
                <input 
                  type="number" 
                  v-model="filters.price_to"
                  placeholder="до"
                  class="w-1/2 px-0 py-2 text-sm border-b border-black/10 bg-transparent focus:outline-none focus:border-black/30 transition-all"
                  @change="applyFilters"
                >
              </div>
            </div>

            <!-- Дополнительные опции -->
            <div>
              <label class="block text-[10px] tracking-[0.2em] uppercase text-black/40 mb-2 font-light">Опции</label>
              <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input type="checkbox" v-model="filters.in_stock" class="w-3 h-3 rounded border-black/20" @change="applyFilters">
                  <span class="text-sm font-light">Только в наличии</span>
                </label>
              </div>
            </div>

            <!-- Сортировка -->
            <div>
              <label class="block text-[10px] tracking-[0.2em] uppercase text-black/40 mb-2 font-light">Сортировка</label>
              <select 
                v-model="filters.sort_by"
                class="w-full px-0 py-2 text-sm border-b border-black/10 bg-transparent focus:outline-none focus:border-black/30 transition-all"
                @change="applyFilters"
              >
                <option value="created_at">По новизне</option>
                <option value="price_asc">Цена (по возрастанию)</option>
                <option value="price_desc">Цена (по убыванию)</option>
                <option value="title_asc">Название (А-Я)</option>
                <option value="title_desc">Название (Я-А)</option>
              </select>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Результаты и пагинация -->
    <div v-if="!loading">
      <!-- Хедер результатов -->
      <div class="flex justify-between items-center mb-6 pb-2 border-b border-black/5">
        <p class="text-xs text-black/40 font-light">
          Найдено <span class="text-black">{{ pagination.total || 0 }}</span> товаров
        </p>
        <div class="flex items-center gap-3">
          <span class="text-[9px] tracking-[0.2em] uppercase text-black/30">Показывать</span>
          <select 
            v-model="perPage"
            class="px-2 py-1 text-xs border border-black/10 rounded-none bg-transparent focus:outline-none"
            @change="changePerPage"
          >
            <option :value="12">12</option>
            <option :value="24">24</option>
            <option :value="48">48</option>
          </select>
        </div>
      </div>

      <!-- Сетка товаров -->
      <div v-if="books.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <BookCard 
          v-for="book in books" 
          :key="book.id"
          :book="book"
          @click="goToProduct(book)"
          @add-to-cart="addToCart"
        />
      </div>
      
      <!-- Пустое состояние -->
      <div v-else class="text-center py-20">
        <i class="fas fa-search text-4xl text-black/10 mb-4"></i>
        <p class="text-black/40 text-sm font-light">Ничего не найдено</p>
        <button @click="resetFilters" class="mt-4 text-black/60 underline text-xs font-light">
          Сбросить фильтры
        </button>
      </div>

      <!-- Пагинация -->
      <div v-if="pagination.last_page > 1" class="flex justify-center mt-12">
        <div class="flex gap-1">
          <button 
            @click="goToPage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="w-8 h-8 flex items-center justify-center text-xs border border-black/10 bg-transparent hover:bg-black/5 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          >
            <i class="fas fa-chevron-left text-[10px]"></i>
          </button>
          
          <template v-for="page in displayedPages" :key="page">
            <button 
              v-if="page !== '...'"
              @click="goToPage(page)"
              class="w-8 h-8 flex items-center justify-center text-xs border transition-all"
              :class="page === pagination.current_page 
                ? 'bg-black text-white border-black' 
                : 'border-black/10 bg-transparent hover:bg-black/5'"
            >
              {{ page }}
            </button>
            <span v-else class="w-8 h-8 flex items-center justify-center text-xs text-black/30">...</span>
          </template>
          
          <button 
            @click="goToPage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="w-8 h-8 flex items-center justify-center text-xs border border-black/10 bg-transparent hover:bg-black/5 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          >
            <i class="fas fa-chevron-right text-[10px]"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Загрузка -->
    <div v-else class="text-center py-20">
      <div class="w-8 h-8 border border-black/20 border-t-black rounded-full animate-spin mx-auto"></div>
      <p class="mt-4 text-black/40 text-sm font-light">Загрузка...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import BookCard from '@/components/ui/BookCard.vue'
import { bookApi } from '@/api/books'
import { genreApi } from '@/api/genres'
import { useCartStore } from '@/stores/cart'

const router = useRouter()
const route = useRoute()
const cartStore = useCartStore()

// Состояния
const loading = ref(false)
const books = ref([])
const categories = ref([])
const pagination = reactive({
  current_page: 1,
  last_page: 1,
  per_page: 12,
  total: 0
})

const perPage = ref(12)

// Фильтры
const filters = reactive({
  search: '',
  category_id: '',
  price_from: '',
  price_to: '',
  in_stock: false,
  sort_by: 'created_at'
})

const showAdvancedFilters = ref(false)

// Подсчет активных фильтров
const activeFiltersCount = computed(() => {
  let count = 0
  if (filters.search) count++
  if (filters.category_id) count++
  if (filters.price_from) count++
  if (filters.price_to) count++
  if (filters.in_stock) count++
  if (filters.sort_by !== 'created_at') count++
  return count
})

// Отображаемые страницы пагинации
const displayedPages = computed(() => {
  const current = pagination.current_page
  const last = pagination.last_page
  const delta = 2
  const range = []
  
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i)
  }
  
  if (current - delta > 2) {
    range.unshift('...')
  }
  if (current + delta < last - 1) {
    range.push('...')
  }
  
  range.unshift(1)
  if (last !== 1) {
    range.push(last)
  }
  
  return range
})

// Получение названия категории
const getCategoryName = (id) => {
  const category = categories.value.find(c => c.id === parseInt(id))
  return category ? category.name : ''
}

// Загрузка категорий
const loadCategories = async () => {
  try {
    const response = await genreApi.getGenreTree()
    if (response.data.success) {
      // Преобразуем дерево в плоский список для фильтра
      const flattenCategories = []
      const flatten = (items) => {
        items.forEach(item => {
          flattenCategories.push({ id: item.id, name: item.name })
          if (item.children) {
            flatten(item.children)
          }
        })
      }
      flatten(response.data.data)
      categories.value = flattenCategories
    }
  } catch (error) {
    console.error('Ошибка загрузки категорий:', error)
  }
}


// Загрузка товаров
const loadBooks = async () => {
  loading.value = true
  
  try {
    const params = {
      per_page: perPage.value,
      page: pagination.current_page,
      sort_by: getSortField(filters.sort_by),
      sort_order: getSortOrder(filters.sort_by)
    }
    
    if (filters.search) params.search = filters.search
    if (filters.category_id) params.category_id = filters.category_id
    if (filters.price_from) params.price_from = filters.price_from
    if (filters.price_to) params.price_to = filters.price_to
    if (filters.in_stock) params.in_stock = 1
    
    console.log('Загрузка товаров с параметрами:', params) // Для отладки
    
    const response = await bookApi.getBooks(params)
    
    if (response.data.success) {
      books.value = response.data.data.data
      pagination.current_page = response.data.data.current_page
      pagination.last_page = response.data.data.last_page
      pagination.per_page = response.data.data.per_page
      pagination.total = response.data.data.total
      console.log('Получено товаров:', books.value.length) // Для отладки
    }
  } catch (error) {
    console.error('Ошибка загрузки товаров:', error)
    books.value = []
  } finally {
    loading.value = false
  }
}

const getSortField = (sortBy) => {
  const mapping = {
    'created_at': 'created_at',
    'price_asc': 'price',
    'price_desc': 'price',
    'title_asc': 'title',
    'title_desc': 'title'
  }
  return mapping[sortBy] || 'created_at'
}

const getSortOrder = (sortBy) => {
  const mapping = {
    'created_at': 'desc',
    'price_asc': 'asc',
    'price_desc': 'desc',
    'title_asc': 'asc',
    'title_desc': 'desc'
  }
  return mapping[sortBy] || 'desc'
}

const applyFilters = () => {
  pagination.current_page = 1
  loadBooks()
  updateUrlParams()
}

const resetFilters = () => {
  filters.search = ''
  filters.category_id = ''
  filters.price_from = ''
  filters.price_to = ''
  filters.in_stock = false
  filters.sort_by = 'created_at'
  pagination.current_page = 1
  loadBooks()
  updateUrlParams()
}

const changePerPage = () => {
  pagination.per_page = perPage.value
  pagination.current_page = 1
  loadBooks()
}

const goToPage = (page) => {
  if (page === pagination.current_page) return
  pagination.current_page = page
  loadBooks()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const goToProduct = (book) => {
  if (book.slug) {
    router.push(`/product/${book.slug}`)
  } else {
    router.push(`/product/${book.id}`)
  }
}

const addToCart = (book) => {
  console.log('Add to cart:', book)
}

const updateUrlParams = () => {
  const query = {}
  
  if (filters.search) query.search = filters.search
  if (filters.category_id) query.category = filters.category_id
  if (filters.price_from) query.price_from = filters.price_from
  if (filters.price_to) query.price_to = filters.price_to
  if (filters.in_stock) query.in_stock = '1'
  if (filters.sort_by !== 'created_at') query.sort = filters.sort_by
  
  router.replace({ query })
}

const loadFiltersFromUrl = () => {
  const query = route.query
  
  if (query.search) filters.search = query.search
  if (query.category) filters.category_id = query.category
  if (query.price_from) filters.price_from = query.price_from
  if (query.price_to) filters.price_to = query.price_to
  if (query.in_stock) filters.in_stock = true
  if (query.sort) filters.sort_by = query.sort
}

onMounted(() => {
  loadFiltersFromUrl()
  loadCategories()
  loadBooks()
})
</script>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  opacity: 0.3;
}

/* Убираем стрелки для number input на мобильных */
input[type="number"] {
  -moz-appearance: textfield;
  appearance: textfield;
}
</style>