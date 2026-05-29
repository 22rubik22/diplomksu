<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Hero секция -->
    <div class="text-center mb-12">
      <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-3 font-light">Discover</p>
      <h1 class="text-3xl sm:text-4xl font-light text-black tracking-tight">Коллекции</h1>
      <div class="w-12 h-px bg-black/20 mx-auto mt-4"></div>
    </div>

    <!-- Состояние загрузки -->
    <div v-if="loading" class="text-center py-20">
      <div class="w-8 h-8 border border-black/20 border-t-black rounded-full animate-spin mx-auto"></div>
      <p class="mt-4 text-black/40 text-sm font-light">Загрузка коллекций...</p>
    </div>

    <!-- Состояние ошибки -->
    <div v-else-if="error" class="text-center py-20">
      <i class="fas fa-exclamation-circle text-3xl text-black/20 mb-4"></i>
      <p class="text-black/40 text-sm">{{ error }}</p>
      <button 
        @click="loadGenres"
        class="mt-6 text-black/60 underline text-xs font-light"
      >
        Попробовать снова
      </button>
    </div>

    <!-- Сетка коллекций -->
    <div v-else-if="rootGenres.length" class="space-y-16">
      <!-- Корневые коллекции -->
      <div v-for="genre in rootGenres" :key="genre.id">
        <!-- Основная коллекция -->
        <div 
          class="group relative cursor-pointer mb-8"
          @click="goToGenre(genre)"
        >
          <div class="aspect-[16/9] sm:aspect-[21/9] bg-[#f8f8f8] overflow-hidden">
            <img 
              v-if="genre.image_url" 
              :src="getImageUrl(genre.image_url)" 
              :alt="genre.name"
              class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105"
            >
            <div v-else class="w-full h-full flex items-center justify-center bg-[#f5f5f5]">
              <i class="fas fa-folder-open text-5xl text-black/10"></i>
            </div>
          </div>
          <div class="absolute inset-0 flex items-end p-6 sm:p-8 bg-gradient-to-t from-black/60 via-transparent to-transparent">
            <div>
              <h3 class="text-white text-2xl sm:text-3xl md:text-4xl font-light tracking-tight mb-2">
                {{ genre.name }}
              </h3>
              <p v-if="genre.description" class="text-white/70 text-xs sm:text-sm max-w-md font-light line-clamp-2">
                {{ genre.description }}
              </p>
              <p v-if="genre.children?.length" class="text-white/50 text-[10px] tracking-wide uppercase mt-3">
                {{ genre.children.length }} коллекций
              </p>
            </div>
          </div>
        </div>

        <!-- Подколлекции (если есть) -->
        <div v-if="genre.children && genre.children.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
          <div 
            v-for="child in genre.children" 
            :key="child.id"
            class="group/sub cursor-pointer"
            @click="goToGenre(child)"
          >
            <div class="aspect-square bg-[#f8f8f8] overflow-hidden mb-3">
              <img 
                v-if="child.image_url" 
                :src="getImageUrl(child.image_url)" 
                :alt="child.name"
                class="w-full h-full object-cover transition-all duration-500 group-hover/sub:scale-105"
              >
              <div v-else class="w-full h-full flex items-center justify-center bg-[#f5f5f5]">
                <i class="fas fa-folder text-2xl text-black/10"></i>
              </div>
            </div>
            <h4 class="text-black/80 text-xs font-light tracking-wide line-clamp-1 text-center">
              {{ child.name }}
            </h4>
            <p v-if="child.children?.length" class="text-black/30 text-[9px] text-center mt-1">
              {{ child.children.length }} подколлекций
            </p>
          </div>
        </div>
      </div>

      <!-- Отдельные коллекции (без родителя) -->
      <div v-if="orphanGenres.length" class="pt-8 border-t border-black/5">
        <div class="text-center mb-8">
          <p class="text-[10px] tracking-[0.3em] uppercase text-black/30">Also in</p>
          <div class="w-8 h-px bg-black/10 mx-auto mt-3"></div>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 sm:gap-6">
          <div 
            v-for="genre in orphanGenres" 
            :key="genre.id"
            class="group/orphan cursor-pointer"
            @click="goToGenre(genre)"
          >
            <div class="aspect-square bg-[#f8f8f8] overflow-hidden mb-3">
              <img 
                v-if="genre.image_url" 
                :src="getImageUrl(genre.image_url)" 
                :alt="genre.name"
                class="w-full h-full object-cover transition-all duration-500 group-hover/orphan:scale-105"
              >
              <div v-else class="w-full h-full flex items-center justify-center bg-[#f5f5f5]">
                <i class="fas fa-folder-open text-3xl text-black/10"></i>
              </div>
            </div>
            <h4 class="text-black/80 text-xs font-light tracking-wide line-clamp-2 text-center">
              {{ genre.name }}
            </h4>
            <p v-if="genre.description" class="text-black/30 text-[9px] text-center mt-1 line-clamp-1">
              {{ genre.description }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Если нет коллекций -->
    <div v-else class="text-center py-20">
      <i class="fas fa-folder-open text-4xl text-black/10 mb-4"></i>
      <p class="text-black/40 text-sm font-light">Коллекции временно отсутствуют</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { genreApi } from '@/api/genres'

const router = useRouter()

const loading = ref(false)
const error = ref(null)
const genresTree = ref([])

const rootGenres = computed(() => {
  return genresTree.value.filter(genre => !genre.parent_id)
})

const orphanGenres = computed(() => {
  const allChildrenIds = new Set()
  
  const collectChildIds = (genre) => {
    if (genre.children) {
      genre.children.forEach(child => {
        allChildrenIds.add(child.id)
        if (child.children) {
          child.children.forEach(grandChild => {
            allChildrenIds.add(grandChild.id)
          })
        }
      })
    }
  }
  
  genresTree.value.forEach(genre => collectChildIds(genre))
  
  return genresTree.value.filter(genre => 
    genre.parent_id && !allChildrenIds.has(genre.id)
  )
})

const loadGenres = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await genreApi.getGenreTree()
    
    if (response.data.success) {
      genresTree.value = response.data.data
    } else {
      error.value = 'Не удалось загрузить коллекции'
    }
  } catch (err) {
    console.error('Ошибка загрузки коллекций:', err)
    error.value = 'Не удалось загрузить коллекции. Попробуйте позже.'
  } finally {
    loading.value = false
  }
}

const getImageUrl = (imagePath) => {
  if (!imagePath) return null
  if (imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
    return imagePath
  }
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  const cleanPath = imagePath.startsWith('/storage') ? imagePath : `/storage/${imagePath.replace(/^\/+/, '')}`
  return `${baseUrl}${cleanPath}`
}

const goToGenre = (genre) => {
  router.push({
    path: '/catalog',
    query: { genre: genre.id }
  })
}

onMounted(() => {
  loadGenres()
})
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Убираем старый заголовок */
.page-title {
  display: none;
}
</style>