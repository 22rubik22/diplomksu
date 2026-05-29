<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Hero секция -->
    <div class="mb-16 sm:mb-20 lg:mb-24 text-center">
      <div class="inline-block mb-6">
        <div class="w-12 h-px bg-black/20 mx-auto mb-4"></div>
        <p class="text-[11px] tracking-[0.3em] uppercase text-black/40 font-light">Summer Collection 2025</p>
        <div class="w-12 h-px bg-black/20 mx-auto mt-4"></div>
      </div>
      
      <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-light text-black tracking-tight leading-[1.1] max-w-4xl mx-auto">
        Стиль, который<br>всегда с вами
      </h1>
      
      <p class="text-black/50 text-sm sm:text-base max-w-md mx-auto mt-6 font-light">
        Эксклюзивные сумки и аксессуары для тех, кто ценит качество и элегантность
      </p>
      
      <button 
        @click="goToCatalog"
        class="mt-10 px-8 py-3 bg-black text-white text-[11px] tracking-[0.2em] uppercase font-light hover:bg-black/80 transition-all"
      >
        Смотреть коллекцию
      </button>
    </div>

    <!-- Секция с рекомендациями -->
    <div class="mb-20">
      <div class="flex justify-between items-end mb-8 border-b border-black/5 pb-4">
        <div>
          <h2 class="text-sm tracking-[0.2em] uppercase text-black/40 font-light mb-1">Editor's pick</h2>
          <h3 class="text-xl sm:text-2xl font-light text-black">Рекомендуем</h3>
        </div>
        <button 
          @click="viewAll('featured')"
          class="text-[10px] tracking-[0.2em] uppercase text-black/40 hover:text-black transition-colors font-light"
        >
          Все →
        </button>
      </div>
      
      <!-- Состояние загрузки -->
      <div v-if="loadingFeatured" class="flex justify-center py-20">
        <div class="w-8 h-8 border border-black/20 border-t-black rounded-full animate-spin"></div>
      </div>
      
      <!-- Состояние ошибки -->
      <div v-else-if="errorFeatured" class="text-center py-20">
        <p class="text-black/40 text-sm">{{ errorFeatured }}</p>
        <button @click="loadFeaturedBooks" class="mt-4 text-black/60 underline text-sm">Повторить</button>
      </div>
      
      <!-- Сетка товаров -->
      <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <BookCard 
          v-for="book in featuredBooks" 
          :key="book.id"
          :book="book"
          @click="goToProduct(book)"
          @add-to-cart="addToCart"
        />
      </div>
      
      <!-- Если нет товаров -->
      <div v-if="!loadingFeatured && featuredBooks.length === 0 && !errorFeatured" class="text-center py-20">
        <p class="text-black/40 text-sm">Товары временно отсутствуют</p>
      </div>
    </div>
    
    <!-- Промо-баннер -->
    <div class="mb-20 bg-[#f5f5f5] py-16 px-8 text-center">
      <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-4">Limited edition</p>
      <h3 class="text-2xl sm:text-3xl font-light text-black mb-4">Капсульная коллекция</h3>
      <p class="text-black/50 text-sm max-w-md mx-auto mb-6">
        Эксклюзивные модели, созданные в единственном экземпляре
      </p>
      <button 
        @click="goToCatalog"
        class="px-8 py-3 border border-black/30 text-black text-[11px] tracking-[0.2em] uppercase font-light hover:bg-black hover:text-white hover:border-black transition-all"
      >
        Узнать больше
      </button>
    </div>
    
    <!-- Секция с новинками -->
    <div class="mb-20">
      <div class="flex justify-between items-end mb-8 border-b border-black/5 pb-4">
        <div>
          <h2 class="text-sm tracking-[0.2em] uppercase text-black/40 font-light mb-1">Just arrived</h2>
          <h3 class="text-xl sm:text-2xl font-light text-black">Новинки</h3>
        </div>
        <button 
          @click="viewAll('new')"
          class="text-[10px] tracking-[0.2em] uppercase text-black/40 hover:text-black transition-colors font-light"
        >
          Все →
        </button>
      </div>
      
      <!-- Состояние загрузки -->
      <div v-if="loadingNew" class="flex justify-center py-20">
        <div class="w-8 h-8 border border-black/20 border-t-black rounded-full animate-spin"></div>
      </div>
      
      <!-- Состояние ошибки -->
      <div v-else-if="errorNew" class="text-center py-20">
        <p class="text-black/40 text-sm">{{ errorNew }}</p>
        <button @click="loadNewBooks" class="mt-4 text-black/60 underline text-sm">Повторить</button>
      </div>
      
      <!-- Сетка товаров -->
      <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        <BookCard 
          v-for="book in newBooks" 
          :key="book.id"
          :book="book"
          @click="goToProduct(book)"
          @add-to-cart="addToCart"
        />
      </div>
      
      <!-- Если нет товаров -->
      <div v-if="!loadingNew && newBooks.length === 0 && !errorNew" class="text-center py-20">
        <p class="text-black/40 text-sm">Новинки скоро появятся</p>
      </div>
    </div>
    
    <!-- Преимущества -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 pt-8 border-t border-black/5">
      <div class="text-center">
        <i class="fas fa-truck text-black/30 text-xl mb-3"></i>
        <p class="text-[10px] tracking-[0.2em] uppercase text-black/40">Бесплатная доставка</p>
        <p class="text-xs text-black/30 mt-1">от 5000 ₽</p>
      </div>
      <div class="text-center">
        <i class="fas fa-undo-alt text-black/30 text-xl mb-3"></i>
        <p class="text-[10px] tracking-[0.2em] uppercase text-black/40">14 дней на возврат</p>
        <p class="text-xs text-black/30 mt-1">100% гарантия</p>
      </div>
      <div class="text-center">
        <i class="fas fa-gem text-black/30 text-xl mb-3"></i>
        <p class="text-[10px] tracking-[0.2em] uppercase text-black/40">Оригинальные бренды</p>
        <p class="text-xs text-black/30 mt-1">Только подлинники</p>
      </div>
      <div class="text-center">
        <i class="fas fa-headset text-black/30 text-xl mb-3"></i>
        <p class="text-[10px] tracking-[0.2em] uppercase text-black/40">Поддержка 24/7</p>
        <p class="text-xs text-black/30 mt-1">Всегда на связи</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import BookCard from '@/components/ui/BookCard.vue'
import { bookApi } from '@/api/books'

const router = useRouter()
const cartStore = useCartStore()

const featuredBooks = ref([])
const loadingFeatured = ref(false)
const errorFeatured = ref(null)

const newBooks = ref([])
const loadingNew = ref(false)
const errorNew = ref(null)

const loadFeaturedBooks = async () => {
  loadingFeatured.value = true
  errorFeatured.value = null
  
  try {
    const response = await bookApi.getFeaturedBooks(8)
    if (response.data.success) {
      featuredBooks.value = response.data.data.data
    } else {
      featuredBooks.value = []
    }
  } catch (error) {
    console.error('Ошибка загрузки рекомендуемых товаров:', error)
    errorFeatured.value = 'Не удалось загрузить рекомендации'
    featuredBooks.value = []
  } finally {
    loadingFeatured.value = false
  }
}

const loadNewBooks = async () => {
  loadingNew.value = true
  errorNew.value = null
  
  try {
    const response = await bookApi.getNewBooks(8)
    if (response.data.success) {
      newBooks.value = response.data.data.data
    } else {
      newBooks.value = []
    }
  } catch (error) {
    console.error('Ошибка загрузки новинок:', error)
    errorNew.value = 'Не удалось загрузить новинки'
    newBooks.value = []
  } finally {
    loadingNew.value = false
  }
}

const goToProduct = (book) => {
  if (book.slug) {
    router.push(`/product/${book.slug}`)
  } else {
    router.push(`/product/${book.id}`)
  }
}

const goToCatalog = () => {
  router.push('/catalog')
}

const viewAll = (type) => {
  router.push({
    path: '/catalog',
    query: type === 'featured' ? { featured: '1' } : { new: '1' }
  })
}

const addToCart = (book) => {
  console.log('Add to cart:', book)
}

onMounted(() => {
  loadFeaturedBooks()
  loadNewBooks()
})
</script>