<template>
  <div>
    <h1 class="page-title">{{ author?.name || 'Автор' }}</h1>
    
    <div v-if="loading" class="text-center py-12 sm:py-16">
      <i class="fas fa-spinner fa-spin text-3xl sm:text-4xl text-[#7f8330]"></i>
      <p class="mt-3 sm:mt-4 text-sm sm:text-base text-[#6c6456]">Загрузка...</p>
    </div>
    
    <div v-else-if="error" class="text-center py-12 sm:py-16">
      <i class="fas fa-exclamation-circle text-3xl sm:text-4xl text-red-500 mb-3 sm:mb-4"></i>
      <p class="text-red-600 text-sm sm:text-base">{{ error }}</p>
      <button @click="loadAuthor" class="mt-3 sm:mt-4 text-[#7f8330] hover:underline text-sm sm:text-base">
        Попробовать снова
      </button>
    </div>
    
    <div v-else-if="author" class="bg-white rounded-xl p-4 sm:p-6 md:p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
      <!-- Информация об авторе -->
      <div class="flex flex-col lg:flex-row gap-6 md:gap-8 mb-8 md:mb-10">
        <!-- Фото -->
        <div class="bg-gradient-to-br from-[#f3d8ce] to-[#f8e6df] rounded-xl flex items-center justify-center p-6 sm:p-8 min-h-[200px] sm:min-h-[250px] lg:min-h-[300px] lg:w-1/3">
          <img 
            v-if="hasValidPhoto"
            :src="author.photo" 
            :alt="author.name"
            class="w-full max-w-[200px] sm:max-w-xs h-auto object-contain rounded-lg shadow-lg"
            @error="photoError = true"
          >
          <div v-else class="text-center">
            <i class="fas fa-user-circle text-6xl sm:text-7xl md:text-8xl text-[#b59b6d]/50"></i>
          </div>
        </div>
        
        <!-- Данные -->
        <div class="lg:w-2/3">
          <h2 class="font-['Playfair_Display'] text-2xl sm:text-3xl md:text-4xl text-[#5e1104] mb-3 sm:mb-4">{{ author.name }}</h2>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 mb-4 sm:mb-6 text-xs sm:text-sm">
            <div v-if="author.birth_date" class="flex items-center gap-2">
              <i class="fas fa-birthday-cake text-[#7f8330] text-sm sm:text-base"></i>
              <span class="text-[#6c6456]">Родился: {{ formatDate(author.birth_date) }}</span>
            </div>
            <div v-if="author.death_date" class="flex items-center gap-2">
              <i class="fas fa-skull text-[#7f8330] text-sm sm:text-base"></i>
              <span class="text-[#6c6456]">Умер: {{ formatDate(author.death_date) }}</span>
            </div>
            <div class="flex items-center gap-2">
              <i class="fas fa-book text-[#7f8330] text-sm sm:text-base"></i>
              <span class="text-[#6c6456]">{{ author.books_count || authorBooks.length }} книг</span>
            </div>
          </div>
          
          <div v-if="author.bio" class="prose prose-stone max-w-none">
            <h3 class="text-[#5e1104] text-base sm:text-lg md:text-xl mb-2 sm:mb-3 font-['Playfair_Display']">Биография</h3>
            <div class="text-xs sm:text-sm text-[#6c6456] leading-relaxed" v-html="author.bio"></div>
          </div>
        </div>
      </div>
      
      <!-- Книги автора -->
      <div v-if="authorBooks.length">
        <h3 class="text-[#5e1104] text-xl sm:text-2xl font-['Playfair_Display'] mb-4 sm:mb-6 pb-2 sm:pb-3 border-b border-[#f3d8ce]">
          Книги автора
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-4 md:gap-5 lg:gap-6">
          <BookCard 
            v-for="book in authorBooks" 
            :key="book.id"
            :book="book"
            @click="goToProduct(book)"
            @add-to-cart="addToCart"
          />
        </div>
      </div>
      
      <div v-else class="text-center py-8 sm:py-12 text-[#6c6456]">
        <i class="fas fa-book-open text-3xl sm:text-4xl mb-2 sm:mb-3 opacity-30"></i>
        <p class="text-sm sm:text-base">У этого автора пока нет книг в каталоге</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { authorApi } from '@/api/authors'
import { useCartStore } from '@/stores/cart'
import BookCard from '@/components/ui/BookCard.vue'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()

const loading = ref(false)
const error = ref(null)
const author = ref(null)
const authorBooks = ref([])
const photoError = ref(false)

const hasValidPhoto = computed(() => {
  return author.value?.photo && 
         typeof author.value.photo === 'string' && 
         author.value.photo.trim() !== '' &&
         !photoError.value
})

// Загрузка автора
const loadAuthor = async () => {
  loading.value = true
  error.value = null
  
  try {
    const authorId = route.params.id
    const response = await authorApi.getAuthor(authorId)
    
    if (response.data.success) {
      author.value = response.data.data
      
      // Проверяем наличие книг в ответе
      if (author.value.books && Array.isArray(author.value.books)) {
        authorBooks.value = author.value.books
      } else {
        authorBooks.value = []
      }
    } else {
      error.value = 'Автор не найден'
    }
  } catch (err) {
    console.error('Ошибка загрузки автора:', err)
    if (err.response?.status === 404) {
      error.value = 'Автор не найден'
    } else {
      error.value = 'Не удалось загрузить информацию об авторе'
    }
  } finally {
    loading.value = false
  }
}

// Форматирование даты
const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

// Переход на страницу книги
const goToProduct = (book) => {
  if (book.slug) {
    router.push(`/product/${book.slug}`)
  } else {
    router.push(`/product/${book.id}`)
  }
}

// Добавление в корзину
const addToCart = (book) => {
  // Функция вызывается из BookCard
  console.log('Add to cart:', book)
}

onMounted(() => {
  loadAuthor()
})
</script>

<style scoped>
.page-title {
  display: none;
}

/* Стили для биографии на мобильных устройствах */
@media (max-width: 640px) {
  .prose {
    font-size: 0.875rem;
  }
  
  .prose h1, .prose h2, .prose h3, .prose h4 {
    font-size: 1.125rem;
  }
}

/* Улучшенная читаемость на мобильных */
.text-[#6c6456] {
  word-break: break-word;
}

/* Анимация для изображения */
img {
  transition: transform 0.3s ease;
}

img:hover {
  transform: scale(1.02);
}

/* Стили для HTML-содержимого биографии */
:deep(p) {
  margin-bottom: 0.75rem;
}

:deep(p:last-child) {
  margin-bottom: 0;
}

:deep(a) {
  color: #7f8330;
  text-decoration: underline;
}

:deep(a:hover) {
  color: #5e1104;
}

:deep(ul), :deep(ol) {
  margin: 0.75rem 0;
  padding-left: 1.5rem;
}

:deep(li) {
  margin-bottom: 0.25rem;
}

:deep(strong) {
  color: #5e1104;
  font-weight: 600;
}
</style>