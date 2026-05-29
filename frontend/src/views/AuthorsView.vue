<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Hero секция -->
    <div class="text-center mb-12">
      <p class="text-[11px] tracking-[0.3em] uppercase text-[#c8a87c] font-light">Brands</p>
      <h1 class="text-3xl sm:text-4xl font-light text-[#2c2c2c] tracking-tight mt-1">Бренды</h1>
      <div class="w-12 h-px bg-[#c8a87c]/30 mx-auto mt-4"></div>
      <p class="text-[#8b7355] text-sm max-w-md mx-auto mt-4 font-light">
        Ведущие мировые бренды сумок и аксессуаров
      </p>
    </div>

    <!-- Поиск и сортировка -->
    <div class="flex flex-col sm:flex-row gap-4 mb-10">
      <div class="flex-1 relative">
        <i class="fas fa-search absolute left-0 top-1/2 -translate-y-1/2 text-[#8b7355] text-sm"></i>
        <input 
          type="text" 
          v-model="searchQuery"
          placeholder="Поиск брендов..." 
          class="w-full pl-6 pr-4 py-2.5 text-sm border-b border-[#e8e0d8] bg-transparent focus:outline-none focus:border-[#c8a87c] transition-all"
          @input="onSearchInput"
        >
      </div>
      
      <div class="flex gap-2">
        <select 
          v-model="sortBy"
          class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer"
          @change="loadBrands"
        >
          <option value="name">По названию (А-Я)</option>
          <option value="name_desc">По названию (Я-А)</option>
          <option value="products_count">По количеству товаров</option>
        </select>
        
        <button 
          @click="resetFilters"
          class="px-4 py-2.5 rounded-xl border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] transition-all"
          title="Сбросить"
        >
          <i class="fas fa-undo-alt text-sm"></i>
        </button>
      </div>
    </div>

    <!-- Состояние загрузки -->
    <div v-if="loading" class="text-center py-20">
      <div class="w-8 h-8 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
      <p class="mt-4 text-[#8b7355] text-sm font-light">Загрузка брендов...</p>
    </div>

    <!-- Состояние ошибки -->
    <div v-else-if="error" class="text-center py-20">
      <i class="fas fa-exclamation-circle text-3xl text-[#e8e0d8] mb-4"></i>
      <p class="text-[#8b7355] text-sm">{{ error }}</p>
      <button @click="loadBrands" class="mt-4 text-[#c8a87c] hover:underline text-sm">
        Повторить
      </button>
    </div>

    <!-- Сетка брендов -->
    <div v-else-if="brands.length" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
      <div 
        v-for="brand in brands" 
        :key="brand.id"
        class="group cursor-pointer"
        @click="goToBrand(brand)"
      >
        <!-- Логотип бренда -->
        <div class="aspect-square rounded-2xl bg-[#faf8f5] border border-[#e8e0d8] flex items-center justify-center overflow-hidden transition-all duration-300 group-hover:border-[#c8a87c] group-hover:shadow-md">
          <img 
            v-if="hasValidPhoto(brand)"
            :src="brand.photo" 
            :alt="brand.name"
            class="w-full h-full object-contain p-4 transition-transform duration-500 group-hover:scale-105"
            loading="lazy"
            @error="onImageError(brand.id)"
          >
          <div v-else class="text-center">
            <i class="fas fa-building text-4xl text-[#e8e0d8]"></i>
          </div>
        </div>
        
        <!-- Информация о бренде -->
        <div class="mt-3 text-center">
          <h3 class="text-[#2c2c2c] text-sm font-medium tracking-wide line-clamp-1">{{ brand.name }}</h3>
          <p class="text-[#8b7355] text-[10px] mt-1">
            {{ brand.country || '—' }}
          </p>
          <p class="text-[#c8a87c] text-[10px] mt-1">
            {{ brand.products_count || 0 }} товаров
          </p>
        </div>
      </div>
    </div>

    <!-- Если нет брендов -->
    <div v-else class="text-center py-20">
      <i class="fas fa-tags text-4xl text-[#e8e0d8] mb-4"></i>
      <p class="text-[#8b7355] text-base font-light">Бренды не найдены</p>
      <p v-if="searchQuery" class="text-[#8b7355] text-sm mt-2">
        По запросу "{{ searchQuery }}" ничего не найдено
      </p>
      <button 
        v-if="searchQuery"
        @click="resetFilters" 
        class="mt-4 text-[#c8a87c] hover:underline text-sm"
      >
        Сбросить поиск
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authorApi } from '@/api/authors'

const router = useRouter()

const loading = ref(false)
const error = ref(null)
const brands = ref([])
const searchQuery = ref('')
const sortBy = ref('name')
const searchTimeout = ref(null)
const photoErrors = ref(new Set())

const hasValidPhoto = (brand) => {
  return brand.photo && 
         typeof brand.photo === 'string' && 
         brand.photo.trim() !== '' &&
         !photoErrors.value.has(brand.id)
}

const onImageError = (brandId) => {
  photoErrors.value.add(brandId)
}

const loadBrands = async () => {
  loading.value = true
  error.value = null
  
  try {
    const params = {}
    
    if (searchQuery.value) params.search = searchQuery.value
    
    switch (sortBy.value) {
      case 'name':
        params.sort_by = 'name'
        params.sort_order = 'asc'
        break
      case 'name_desc':
        params.sort_by = 'name'
        params.sort_order = 'desc'
        break
      case 'products_count':
        params.sort_by = 'products_count'
        params.sort_order = 'desc'
        break
      default:
        params.sort_by = 'name'
        params.sort_order = 'asc'
    }
    
    const response = await authorApi.getAuthors(params)
    
    if (response.data.success) {
      brands.value = response.data.data
    } else {
      error.value = 'Не удалось загрузить бренды'
    }
  } catch (err) {
    console.error('Ошибка загрузки брендов:', err)
    error.value = 'Не удалось загрузить бренды. Попробуйте позже.'
  } finally {
    loading.value = false
  }
}

const onSearchInput = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => loadBrands(), 500)
}

const resetFilters = () => {
  searchQuery.value = ''
  sortBy.value = 'name'
  loadBrands()
}

const goToBrand = (brand) => {
  router.push(`/catalog?brand=${brand.id}`)
}

onMounted(() => loadBrands())
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>