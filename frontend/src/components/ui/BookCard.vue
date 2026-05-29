<template>
  <div 
    class="group relative bg-white overflow-hidden transition-all duration-500 hover:shadow-2xl cursor-pointer"
    @click="handleClick"
  >
    <!-- Контейнер с изображением во всю ширину -->
    <div class="relative overflow-hidden bg-[#f8f8f8]">
      <!-- Бейджики в левом верхнем углу -->
      <div class="absolute top-4 left-4 z-10 flex flex-col gap-2">
        <span v-if="book.is_bestseller" class="backdrop-blur-md bg-black/60 text-white text-[10px] tracking-[0.2em] px-3 py-1.5 uppercase font-light">
          Bestseller
        </span>
        <span v-if="book.is_new" class="backdrop-blur-md bg-white/90 text-black text-[10px] tracking-[0.2em] px-3 py-1.5 uppercase font-light">
          New
        </span>
        <span v-if="book.discount_percent" class="backdrop-blur-md bg-rose-600/90 text-white text-[10px] tracking-[0.2em] px-3 py-1.5 uppercase font-light">
          -{{ book.discount_percent }}%
        </span>
      </div>
      
      <!-- Кнопка избранного -->
      <button 
        @click.stop="toggleFavorite"
        class="absolute top-4 right-4 z-10 w-10 h-10 rounded-full bg-white/80 backdrop-blur-sm flex items-center justify-center transition-all hover:bg-white hover:scale-110"
      >
        <i :class="isFavorite ? 'fas fa-heart text-rose-500' : 'far fa-heart text-black/60'" class="text-lg"></i>
      </button>

      <!-- Изображение -->
      <div class="aspect-[3/4] flex items-center justify-center p-8">
        <img 
          v-if="hasValidCover"
          :src="book.cover_image" 
          :alt="book.title"
          class="w-full h-full object-contain transition-all duration-700 group-hover:scale-105"
          @error="onImageError"
          @load="onImageLoad"
        >
        <div v-else class="w-full h-full flex flex-col items-center justify-center">
          <i class="fas fa-bag-shopping text-5xl text-black/10"></i>
        </div>
      </div>
    </div>

    <!-- Информация о товаре -->
    <div class="p-5 text-center">
      <!-- Бренд (author_name) -->
      <p class="text-[10px] tracking-[0.2em] uppercase text-black/40 mb-2 font-light">
        {{ getAuthorName() }}
      </p>
      
      <!-- Название -->
      <h3 class="text-sm font-light text-black/80 leading-relaxed line-clamp-2 mb-3 px-2">
        {{ book.title }}
      </h3>
      
      <!-- Категории (genres) -->
      <p v-if="book.genres && book.genres.length" class="text-[9px] tracking-wide text-black/30 uppercase mb-4">
        {{ getGenresList() }}
      </p>
      
      <!-- Цена -->
      <div class="mb-5">
        <span class="text-lg font-light text-black">{{ formatPrice(getCurrentPrice()) }} ₽</span>
        <span v-if="book.old_price" class="text-xs text-black/30 line-through ml-2">{{ formatPrice(book.old_price) }} ₽</span>
      </div>
      
      <!-- Кнопка "В корзину" -->
      <button 
        v-if="!isInCart"
        @click.stop="addToCart"
        :disabled="!isInStock || addingToCart"
        class="w-full py-3 bg-black text-white text-[11px] tracking-[0.2em] uppercase font-light transition-all hover:bg-black/80 disabled:opacity-30 disabled:cursor-not-allowed"
      >
        <span v-if="!addingToCart">Добавить в корзину</span>
        <i v-else class="fas fa-spinner fa-spin"></i>
      </button>
      
      <!-- Кнопка "В корзине" -->
      <button 
        v-else
        @click.stop="goToCart"
        class="w-full py-3 bg-black/10 text-black text-[11px] tracking-[0.2em] uppercase font-light transition-all hover:bg-black/20 flex items-center justify-center gap-2"
      >
        <i class="fas fa-check text-xs"></i>
        В корзине · {{ cartItemQuantity }}
      </button>
    </div>

    <!-- Уведомление -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="showToast" class="fixed bottom-6 right-6 z-50 bg-black text-white px-5 py-3 text-xs tracking-wide flex items-center gap-3 shadow-xl">
          <i :class="toastType === 'success' ? 'fas fa-check' : 'fas fa-exclamation'"></i>
          <span>{{ toastMessage }}</span>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useCartStore } from '@/stores/cart'
import { useFavoritesStore } from '@/stores/favorites'
import { useRouter } from 'vue-router'

const props = defineProps({
  book: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['click', 'add-to-cart', 'update-cart-status'])

const cartStore = useCartStore()
const favoritesStore = useFavoritesStore()
const router = useRouter()

const imageLoadError = ref(false)
const addingToCart = ref(false)
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')
const isInCart = ref(false)
const cartItemQuantity = ref(0)

const isFavorite = computed(() => favoritesStore.isBookFavorite(props.book.id))

const hasValidCover = computed(() => {
  return props.book.cover_image && 
         typeof props.book.cover_image === 'string' && 
         props.book.cover_image.trim() !== '' &&
         !imageLoadError.value
})

const isInStock = computed(() => {
  if (props.book.is_in_stock !== undefined) {
    return props.book.is_in_stock
  }
  if (props.book.quantity !== undefined) {
    return props.book.quantity > 0
  }
  return true
})

const checkIfInCart = () => {
  const cartItem = cartStore.items.find(item => item.book_id === props.book.id)
  
  if (cartItem) {
    isInCart.value = true
    cartItemQuantity.value = cartItem.quantity
  } else {
    isInCart.value = false
    cartItemQuantity.value = 0
  }
}

watch(
  () => cartStore.items,
  (newItems, oldItems) => {
    if (newItems !== oldItems) {
      checkIfInCart()
    }
  },
  { deep: true, immediate: true }
)

const formatPrice = (price) => {
  if (!price && price !== 0) return '0'
  return new Intl.NumberFormat('ru-RU').format(price)
}

const getCurrentPrice = () => {
  return props.book.price || 0
}

const getAuthorName = () => {
  if (props.book.author_name) {
    return props.book.author_name
  }
  if (props.book.author) {
    if (typeof props.book.author === 'object') {
      return props.book.author.name || 'Brand'
    }
    if (typeof props.book.author === 'string') {
      return props.book.author
    }
  }
  return 'Brand'
}

const getGenresList = () => {
  if (props.book.genres && Array.isArray(props.book.genres) && props.book.genres.length > 0) {
    return props.book.genres.map(g => g.name).join(' / ')
  }
  return ''
}

const showNotification = (message, type = 'success') => {
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => {
    showToast.value = false
  }, 2000)
}

const addToCart = async () => {
  if (!isInStock.value || addingToCart.value) {
    return
  }
  
  addingToCart.value = true
  
  const bookForCart = {
    id: props.book.id,
    title: props.book.title,
    price: getCurrentPrice(),
    cover_image: props.book.cover_image,
    is_in_stock: isInStock.value,
    author_name: getAuthorName()
  }
  
  const result = await cartStore.addToCart(bookForCart, 1)
  
  addingToCart.value = false
  
  if (result.success) {
    await nextTick()
    await nextTick()
    
    checkIfInCart()
    showNotification(result.message || 'Добавлено в корзину')
    emit('add-to-cart', props.book)
    emit('update-cart-status', { bookId: props.book.id, inCart: true })
  } else {
    showNotification(result.message || 'Ошибка', 'error')
  }
}

const toggleFavorite = async (e) => {
  e?.stopPropagation()
  
  if (isFavorite.value) {
    const result = await favoritesStore.removeFromFavorites(props.book.id)
    if (result.success) {
      showNotification('Удалено из избранного')
    } else {
      showNotification(result.message, 'error')
    }
  } else {
    const result = await favoritesStore.addToFavorites(props.book.id)
    if (result.success) {
      showNotification('Добавлено в избранное')
    } else {
      showNotification(result.message, 'error')
    }
  }
}

const goToCart = () => {
  router.push('/cart')
}

const handleClick = () => {
  emit('click', props.book)
}

const onImageError = () => {
  imageLoadError.value = true
}

const onImageLoad = () => {
  imageLoadError.value = false
}

onMounted(() => {
  checkIfInCart()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(100%);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>