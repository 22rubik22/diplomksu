<template>
  <div>
    <h1 class="page-title">Избранное</h1>
    
    <div class="bg-white rounded-xl p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
      <!-- Состояние загрузки -->
      <div v-if="loading" class="text-center py-16">
        <i class="fas fa-spinner fa-spin text-4xl text-[#7f8330]"></i>
        <p class="mt-4 text-[#6c6456]">Загрузка избранных книг...</p>
      </div>
      
      <!-- Ошибка -->
      <div v-else-if="error" class="text-center py-16">
        <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-4"></i>
        <p class="text-red-600">{{ error }}</p>
        <button @click="loadFavorites" class="mt-4 text-[#7f8330] hover:underline">
          Попробовать снова
        </button>
      </div>
      
      <!-- Список книг -->
      <div v-else-if="favorites.length" class="books-grid">
        <BookCard 
          v-for="book in favorites" 
          :key="book.id"
          :book="book"
          @click="goToProduct(book)"
        />
      </div>
      
      <!-- Пустой список -->
      <div v-else class="text-center py-16">
        <i class="far fa-heart text-6xl text-[#7f8330]/30 mb-4"></i>
        <p class="text-xl text-[#6c6456] mb-4">В избранном пока нет книг</p>
        <router-link to="/catalog" class="inline-block btn-primary px-8 py-3">
          Перейти в каталог
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useFavoritesStore } from '@/stores/favorites'
import BookCard from '@/components/ui/BookCard.vue'

const router = useRouter()
const favoritesStore = useFavoritesStore()

const loading = ref(false)
const error = ref(null)
const favorites = ref([])

const loadFavorites = async () => {
  loading.value = true
  error.value = null
  
  try {
    await favoritesStore.loadFavoritesFull()
    favorites.value = favoritesStore.items
  } catch (err) {
    console.error('Ошибка загрузки избранного:', err)
    error.value = err.response?.data?.message || 'Не удалось загрузить избранное'
  } finally {
    loading.value = false
  }
}

const goToProduct = (book) => {
  if (book.slug) {
    router.push(`/product/${book.slug}`)
  } else {
    router.push(`/product/${book.id}`)
  }
}

// Добавляем watch для обновления списка при изменении store
watch(
  () => favoritesStore.items,
  (newItems) => {
    favorites.value = newItems
  },
  { deep: true }
)

onMounted(() => {
  loadFavorites()
})
</script>

<style scoped>
.books-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 2rem;
}
</style>