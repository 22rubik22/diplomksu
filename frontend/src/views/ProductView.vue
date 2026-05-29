<template>
  <div v-if="book" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Хлебные крошки -->
    <div class="mb-8 text-[10px] tracking-[0.2em] uppercase text-black/30">
      <span class="hover:text-black/60 cursor-pointer" @click="$router.push('/')">Главная</span>
      <span class="mx-2">/</span>
      <span class="hover:text-black/60 cursor-pointer" @click="$router.push('/catalog')">Каталог</span>
      <span class="mx-2">/</span>
      <span class="text-black/60">{{ book.title }}</span>
    </div>

    <div class="flex flex-col lg:flex-row gap-8 md:gap-12">
      <!-- Изображение товара -->
      <div class="lg:w-1/2">
        <div class="bg-[#f8f8f8] flex items-center justify-center p-8 min-h-[400px] sm:min-h-[500px]">
          <img 
            v-if="hasValidCover"
            :src="book.cover_image" 
            :alt="book.title"
            class="w-full max-h-[500px] object-contain"
            @error="onImageError"
          >
          <div v-else class="text-center">
            <i class="fas fa-shopping-bag text-6xl text-black/10"></i>
          </div>
        </div>
      </div>

      <!-- Информация о товаре -->
      <div class="lg:w-1/2">
        <!-- Бренд (автор) -->
        <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-3 font-light">
          {{ getAuthorName() }}
        </p>
        
        <!-- Название -->
        <h1 class="text-2xl sm:text-3xl md:text-4xl font-light text-black tracking-tight mb-4">
          {{ book.title }}
        </h1>
        
        <!-- Категории (жанры) -->
        <p v-if="book.genres && book.genres.length" class="text-xs text-black/30 mb-6">
          {{ getGenresList() }}
        </p>
        
        <!-- Предупреждение о 18+ -->
        <div 
          v-if="book.has_remarks && book.remarks" 
          class="mb-6 p-4 bg-[#f8f8f8] border-l border-black/10"
        >
          <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-black/30 text-sm mt-0.5"></i>
            <div>
              <p class="text-black/60 text-xs font-light">Содержит: {{ getActiveRemarksString() }}</p>
              <p class="text-black/40 text-[10px] mt-1">Требуется подтверждение возраста 18+</p>
            </div>
          </div>
        </div>
        
        <!-- Цена и наличие -->
        <div class="mb-8 pb-6 border-b border-black/5">
          <div class="flex items-baseline gap-3 mb-3">
            <span class="text-3xl sm:text-4xl font-light text-black">{{ formatPrice(book.price) }} ₽</span>
            <span v-if="book.old_price" class="text-sm text-black/30 line-through">{{ formatPrice(book.old_price) }} ₽</span>
            <span v-if="book.discount_percent" class="text-[10px] tracking-wide text-black/40 border border-black/10 px-2 py-0.5">
              -{{ book.discount_percent }}%
            </span>
          </div>
          <p class="text-xs" :class="isInStock ? 'text-black/40' : 'text-black/20'">
            <i :class="isInStock ? 'fas fa-check' : 'fas fa-times'" class="mr-2 text-[10px]"></i>
            {{ isInStock ? 'В наличии' : 'Нет в наличии' }}
          </p>
        </div>

        <!-- Количество и кнопки -->
        <div v-if="!book.has_remarks || ageConfirmed" class="mb-8">
          <div class="flex flex-wrap gap-4 items-center">
            <!-- Количество -->
            <div v-if="!isInCart" class="flex items-center border border-black/10">
              <button 
                @click="decrementQuantity"
                class="w-10 h-10 flex items-center justify-center hover:bg-black/5 transition-colors disabled:opacity-30"
                :disabled="quantity <= 1"
              >
                <i class="fas fa-minus text-[10px]"></i>
              </button>
              <span class="w-12 text-center text-sm">{{ quantity }}</span>
              <button 
                @click="incrementQuantity"
                class="w-10 h-10 flex items-center justify-center hover:bg-black/5 transition-colors disabled:opacity-30"
                :disabled="quantity >= (book.quantity || 999)"
              >
                <i class="fas fa-plus text-[10px]"></i>
              </button>
            </div>
            
            <!-- Кнопка корзины -->
            <button 
              v-if="!isInCart"
              @click="handleAddToCart"
              :disabled="!isInStock || addingToCart"
              class="flex-1 sm:flex-none px-8 py-3 bg-black text-white text-[11px] tracking-[0.2em] uppercase font-light hover:bg-black/80 transition-all disabled:opacity-30"
            >
              <i v-if="addingToCart" class="fas fa-spinner fa-spin mr-2"></i>
              Добавить в корзину
            </button>
            
            <!-- Кнопки когда в корзине -->
            <div v-else class="flex items-center gap-3">
              <div class="flex items-center border border-black/10">
                <button 
                  @click="updateCartQuantity(cartItemQuantity - 1)"
                  class="w-10 h-10 flex items-center justify-center hover:bg-black/5 transition-colors"
                  :disabled="cartItemQuantity <= 1"
                >
                  <i class="fas fa-minus text-[10px]"></i>
                </button>
                <span class="w-12 text-center text-sm">{{ cartItemQuantity }}</span>
                <button 
                  @click="updateCartQuantity(cartItemQuantity + 1)"
                  class="w-10 h-10 flex items-center justify-center hover:bg-black/5 transition-colors"
                  :disabled="cartItemQuantity >= (book.quantity || 999)"
                >
                  <i class="fas fa-plus text-[10px]"></i>
                </button>
              </div>
              <button 
                @click="removeFromCart"
                class="w-10 h-10 flex items-center justify-center border border-black/10 hover:bg-black/5 transition-colors"
              >
                <i class="fas fa-trash-alt text-xs text-black/40"></i>
              </button>
            </div>
            
            <!-- Избранное -->
            <button 
              @click="toggleFavorite"
              class="w-10 h-10 flex items-center justify-center border border-black/10 hover:bg-black/5 transition-colors"
              :class="{ 'text-black/60': isFavorite }"
            >
              <i :class="isFavorite ? 'fas fa-heart' : 'far fa-heart'" class="text-sm"></i>
            </button>
          </div>
        </div>
        
        <!-- Заблокированные кнопки для 18+ -->
        <div v-else class="mb-8 p-4 bg-[#f8f8f8] text-center">
          <p class="text-black/40 text-xs mb-3">
            <i class="fas fa-lock mr-1"></i>
            Для покупки требуется подтверждение возраста 18+
          </p>
          <button 
            @click="showAgeModal = true"
            class="px-8 py-3 bg-black text-white text-[11px] tracking-[0.2em] uppercase font-light"
          >
            Подтвердить возраст
          </button>
        </div>

        <!-- Краткое описание -->
        <div v-if="book.short_description" class="mb-6">
          <p class="text-black/50 text-sm font-light leading-relaxed">
            {{ book.short_description }}
          </p>
        </div>

        <!-- Характеристики -->
        <div class="pt-6 border-t border-black/5">
          <h3 class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-4 font-light">Характеристики</h3>
          <dl class="space-y-2 text-sm">
            <div v-if="book.material" class="flex">
              <dt class="w-24 text-black/40 font-light">Материал</dt>
              <dd class="text-black/60 font-light">{{ book.material }}</dd>
            </div>
            <div v-if="book.color" class="flex">
              <dt class="w-24 text-black/40 font-light">Цвет</dt>
              <dd class="text-black/60 font-light">{{ book.color }}</dd>
            </div>
            <div v-if="book.size" class="flex">
              <dt class="w-24 text-black/40 font-light">Размер</dt>
              <dd class="text-black/60 font-light">{{ book.size }}</dd>
            </div>
            <div v-if="book.brand" class="flex">
              <dt class="w-24 text-black/40 font-light">Бренд</dt>
              <dd class="text-black/60 font-light">{{ book.brand }}</dd>
            </div>
            <div v-if="book.country" class="flex">
              <dt class="w-24 text-black/40 font-light">Страна</dt>
              <dd class="text-black/60 font-light">{{ book.country }}</dd>
            </div>
          </dl>
        </div>
      </div>
    </div>

    <!-- Полное описание -->
    <div v-if="book.description" class="mt-12 pt-8 border-t border-black/5">
      <h3 class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-4 font-light">Описание</h3>
      <p class="text-black/50 text-sm font-light leading-relaxed whitespace-pre-line">
        {{ book.description }}
      </p>
    </div>

    <!-- Похожие товары -->
    <div v-if="similarBooks.length > 0" class="mt-16">
      <div class="text-center mb-8">
        <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-2 font-light">You may also like</p>
        <h3 class="text-xl font-light text-black tracking-tight">Вам может понравиться</h3>
        <div class="w-8 h-px bg-black/20 mx-auto mt-3"></div>
      </div>
      
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
        <BookCard 
          v-for="similar in similarBooks" 
          :key="similar.id"
          :book="similar"
          @click="goToProduct(similar)"
        />
      </div>
    </div>

    <!-- Отзывы -->
    <div class="mt-16 pt-8 border-t border-black/5">
      <div class="text-center mb-8">
        <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-2 font-light">Reviews</p>
        <h3 class="text-xl font-light text-black tracking-tight">Отзывы</h3>
        <div class="w-8 h-px bg-black/20 mx-auto mt-3"></div>
      </div>

      <!-- Средняя оценка -->
      <div class="text-center mb-8">
        <div class="text-4xl font-light text-black">{{ averageRating.toFixed(1) }}</div>
        <div class="flex justify-center gap-1 my-2">
          <i v-for="i in 5" :key="i" :class="i <= Math.round(averageRating) ? 'fas fa-star text-black/60' : 'far fa-star text-black/20'" class="text-sm"></i>
        </div>
        <p class="text-[10px] text-black/30">{{ reviews.length }} отзыва</p>
      </div>

      <!-- Форма отзыва -->
      <div v-if="isLoggedIn" class="max-w-2xl mx-auto mb-10 p-6 bg-[#f8f8f8]">
        <h4 class="text-xs tracking-[0.2em] uppercase text-black/40 mb-4 font-light">
          {{ userReview ? 'Редактировать отзыв' : 'Оставить отзыв' }}
        </h4>
        
        <div v-if="!canReview && !userReview" class="mb-4 p-3 bg-white/50 text-center">
          <p class="text-black/40 text-xs">Вы можете оставить отзыв только после покупки товара</p>
        </div>
        
        <form v-else-if="canReview || userReview" @submit.prevent="submitReview">
          <div class="flex justify-center gap-2 mb-4">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="reviewForm.rating = star"
              class="text-xl transition-colors"
              :class="star <= reviewForm.rating ? 'text-black/60' : 'text-black/20'"
            >
              <i class="fas fa-star"></i>
            </button>
          </div>
          
          <input
            type="text"
            v-model="reviewForm.title"
            placeholder="Заголовок"
            class="w-full px-0 py-2 mb-3 text-sm border-b border-black/10 bg-transparent focus:outline-none focus:border-black/30 transition-all"
          >
          
          <textarea
            v-model="reviewForm.comment"
            rows="3"
            placeholder="Ваш отзыв..."
            class="w-full px-0 py-2 text-sm border-b border-black/10 bg-transparent focus:outline-none focus:border-black/30 transition-all resize-none"
          ></textarea>
          
          <div class="flex gap-3 mt-4">
            <button
              type="submit"
              class="px-6 py-2 bg-black text-white text-[10px] tracking-[0.2em] uppercase font-light hover:bg-black/80 transition-all"
              :disabled="submittingReview || reviewForm.rating === 0"
            >
              {{ userReview ? 'Сохранить' : 'Отправить' }}
            </button>
            <button
              v-if="userReview"
              type="button"
              @click="cancelEdit"
              class="px-6 py-2 border border-black/20 text-black/40 text-[10px] tracking-[0.2em] uppercase font-light hover:bg-black/5 transition-all"
            >
              Отмена
            </button>
          </div>
        </form>
      </div>
      
      <div v-else class="text-center mb-10">
        <p class="text-black/40 text-sm">
          <a href="/login" class="underline hover:text-black/60">Войдите</a> чтобы оставить отзыв
        </p>
      </div>

      <!-- Список отзывов -->
      <div v-if="reviews.length > 0" class="max-w-2xl mx-auto space-y-6">
        <div v-for="review in reviews" :key="review.id" class="border-b border-black/5 pb-4 last:border-0">
          <div class="flex justify-between items-start mb-2">
            <div>
              <span class="text-sm font-light text-black/60">{{ review.user_name }}</span>
              <span class="text-[10px] text-black/30 ml-2">{{ formatDate(review.created_at) }}</span>
            </div>
            <div class="flex gap-0.5">
              <i v-for="i in 5" :key="i" :class="i <= review.rating ? 'fas fa-star text-black/40' : 'far fa-star text-black/20'" class="text-[10px]"></i>
            </div>
          </div>
          <h5 v-if="review.title" class="text-sm font-light text-black/60 mb-1">{{ review.title }}</h5>
          <p class="text-xs text-black/40 font-light">{{ review.comment }}</p>
        </div>
      </div>
      
      <div v-else class="text-center py-8">
        <p class="text-black/30 text-sm font-light">Отзывов пока нет. Будьте первым!</p>
      </div>
    </div>

    <!-- Модальное окно 18+ -->
    <div v-if="showAgeModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeAgeModal">
      <div class="bg-white p-8 max-w-md mx-4 text-center">
        <i class="fas fa-exclamation-triangle text-3xl text-black/40 mb-4"></i>
        <h3 class="text-lg font-light mb-3">Подтверждение возраста</h3>
        <p class="text-black/50 text-sm font-light mb-6">
          Данный товар содержит материалы, предназначенные только для лиц старше 18 лет
        </p>
        <div class="flex gap-3">
          <button 
            @click="confirmAge"
            class="flex-1 px-6 py-2 bg-black text-white text-[10px] tracking-[0.2em] uppercase font-light hover:bg-black/80 transition-all"
          >
            Мне есть 18
          </button>
          <button 
            @click="closeAgeModal"
            class="flex-1 px-6 py-2 border border-black/20 text-black/40 text-[10px] tracking-[0.2em] uppercase font-light hover:bg-black/5 transition-all"
          >
            Нет
          </button>
        </div>
      </div>
    </div>

    <!-- Уведомление -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="showToast" class="fixed bottom-6 right-6 z-50 bg-black text-white px-5 py-3 text-xs tracking-wide shadow-xl flex items-center gap-3">
          <i :class="toastType === 'success' ? 'fas fa-check' : 'fas fa-exclamation'"></i>
          <span>{{ toastMessage }}</span>
        </div>
      </Transition>
    </Teleport>
  </div>
  
  <!-- Загрузка -->
  <div v-else-if="loading" class="text-center py-20">
    <div class="w-8 h-8 border border-black/20 border-t-black rounded-full animate-spin mx-auto"></div>
  </div>
  
  <!-- Ошибка -->
  <div v-else-if="error" class="text-center py-20">
    <i class="fas fa-exclamation-circle text-3xl text-black/20 mb-4"></i>
    <p class="text-black/40 text-sm">{{ error }}</p>
    <button @click="loadBook" class="mt-4 text-black/60 underline text-xs">Повторить</button>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useFavoritesStore } from '@/stores/favorites'
import { bookApi } from '@/api/books'
import { reviewApi } from '@/api/reviews'
import BookCard from '@/components/ui/BookCard.vue'

const route = useRoute()
const router = useRouter()
const cartStore = useCartStore()
const favoritesStore = useFavoritesStore()

const book = ref(null)
const loading = ref(false)
const error = ref(null)
const quantity = ref(1)
const isFavorite = ref(false)
const reviews = ref([])
const similarBooks = ref([])
const imageLoadError = ref(false)
const addingToCart = ref(false)
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const ageConfirmed = ref(false)
const showAgeModal = ref(false)

const isInCart = ref(false)
const cartItemQuantity = ref(0)

const isLoggedIn = ref(false)
const userReview = ref(null)
const canReview = ref(false)
const submittingReview = ref(false)
const reviewForm = ref({ rating: 0, title: '', comment: '' })

const hasValidCover = computed(() => {
  return book.value?.cover_image && !imageLoadError.value
})

const isInStock = computed(() => {
  if (!book.value) return false
  if (book.value.is_in_stock !== undefined) return book.value.is_in_stock
  if (book.value.quantity !== undefined) return book.value.quantity > 0
  return true
})

const averageRating = computed(() => {
  if (book.value?.average_rating) return book.value.average_rating
  if (reviews.value.length === 0) return 0
  const sum = reviews.value.reduce((acc, r) => acc + r.rating, 0)
  return sum / reviews.value.length
})

const getActiveRemarksList = () => {
  if (!book.value?.remarks) return []
  const remarks = []
  if (book.value.remarks.foul_language) remarks.push('нецензурная лексика')
  if (book.value.remarks.drugs) remarks.push('упоминание наркотиков')
  if (book.value.remarks.sex_and_eroticism) remarks.push('секс и эротика')
  if (book.value.remarks.brutality_and_violence) remarks.push('жестокость')
  return remarks
}

const getActiveRemarksString = () => {
  const list = getActiveRemarksList()
  if (list.length === 0) return '—'
  return list.join(', ')
}

const checkIfInCart = () => {
  const cartItem = cartStore.items.find(item => item.book_id === book.value?.id)
  if (cartItem) {
    isInCart.value = true
    cartItemQuantity.value = cartItem.quantity
  } else {
    isInCart.value = false
    cartItemQuantity.value = 0
  }
}

watch(() => cartStore.items, () => checkIfInCart(), { deep: true })

const showNotification = (message, type = 'success') => {
  toastMessage.value = message
  toastType.value = type
  showToast.value = true
  setTimeout(() => showToast.value = false, 2500)
}

const loadBook = async () => {
  loading.value = true
  error.value = null
  try {
    const slug = route.params.id
    const response = await bookApi.getBookBySlug(slug)
    if (response.data.success) {
      book.value = response.data.data
      if (book.value.has_remarks) {
        const confirmed = localStorage.getItem(`age_confirmed_${book.value.id}`)
        if (confirmed === 'true') ageConfirmed.value = true
        else showAgeModal.value = true
      }
      await loadSimilarBooks()
      checkIfInCart()
    } else {
      error.value = 'Товар не найден'
    }
  } catch (err) {
    error.value = 'Не удалось загрузить товар'
  } finally {
    loading.value = false
  }
}

const confirmAge = () => {
  ageConfirmed.value = true
  showAgeModal.value = false
  if (book.value?.id) localStorage.setItem(`age_confirmed_${book.value.id}`, 'true')
}

const closeAgeModal = () => {
  showAgeModal.value = false
}

const loadSimilarBooks = async () => {
  if (!book.value?.id) return
  try {
    const response = await bookApi.getSimilarBooks(book.value.id, 4)
    if (response.data.success) similarBooks.value = response.data.data
  } catch (err) {
    console.error('Ошибка загрузки похожих товаров:', err)
  }
}

const loadReviews = async () => {
  if (!book.value?.id) return
  try {
    const response = await reviewApi.getBookReviews(book.value.id, { per_page: 50 })
    if (response.data.success) {
      reviews.value = response.data.data.reviews?.data || response.data.data.reviews || []
    }
  } catch (err) {
    console.error('Ошибка загрузки отзывов:', err)
  }
}

const loadReviewPermission = async () => {
  if (!book.value?.id) return
  try {
    const response = await reviewApi.getReviewPermission(book.value.id)
    if (response.data.success) {
      isLoggedIn.value = response.data.data.is_logged_in
      canReview.value = response.data.data.can_review
      if (response.data.data.user_review) {
        userReview.value = response.data.data.user_review
        reviewForm.value = {
          rating: userReview.value.rating,
          title: userReview.value.title || '',
          comment: userReview.value.comment || ''
        }
      }
    }
  } catch (err) {
    isLoggedIn.value = false
  }
}

const getAuthorName = () => {
  if (!book.value?.author) return 'Brand'
  if (typeof book.value.author === 'object') return book.value.author.name || 'Brand'
  if (typeof book.value.author === 'string') return book.value.author
  return 'Brand'
}

const getGenresList = () => {
  if (book.value?.genres && Array.isArray(book.value.genres)) {
    return book.value.genres.map(g => g.name).join(' / ')
  }
  return ''
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0'
  return new Intl.NumberFormat('ru-RU').format(price)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ru-RU')
}

const onImageError = () => {
  imageLoadError.value = true
}

const incrementQuantity = () => {
  if (book.value && quantity.value < (book.value.quantity || 999)) quantity.value++
}

const decrementQuantity = () => {
  if (quantity.value > 1) quantity.value--
}

const updateCartQuantity = async (newQuantity) => {
  if (!book.value) return
  const cartItem = cartStore.items.find(item => item.book_id === book.value.id)
  if (!cartItem) return
  if (newQuantity <= 0) {
    const result = await cartStore.removeItem(cartItem.id)
    if (result.success) {
      showNotification('Товар удален из корзины')
      checkIfInCart()
    }
  } else {
    const result = await cartStore.updateQuantity(cartItem.id, newQuantity)
    if (result.success) {
      cartItemQuantity.value = newQuantity
      showNotification('Количество обновлено')
    }
  }
}

const removeFromCart = async () => {
  if (!book.value) return
  const cartItem = cartStore.items.find(item => item.book_id === book.value.id)
  if (!cartItem) return
  const result = await cartStore.removeItem(cartItem.id)
  if (result.success) {
    showNotification('Товар удален из корзины')
    checkIfInCart()
  }
}

const handleAddToCart = async () => {
  if (!book.value || !isInStock.value || addingToCart.value) return
  if (book.value.has_remarks && !ageConfirmed.value) {
    showAgeModal.value = true
    return
  }
  addingToCart.value = true
  try {
    const bookForCart = {
      id: book.value.id,
      title: book.value.title,
      price: book.value.price,
      cover_image: book.value.cover_image,
      is_in_stock: isInStock.value,
      author_name: getAuthorName()
    }
    const result = await cartStore.addToCart(bookForCart, quantity.value)
    if (result.success) {
      showNotification('Товар добавлен в корзину')
      checkIfInCart()
      quantity.value = 1
    }
  } finally {
    addingToCart.value = false
  }
}

const toggleFavorite = async () => {
  if (!book.value) return
  const result = isFavorite.value
    ? await favoritesStore.removeFromFavorites(book.value.id)
    : await favoritesStore.addToFavorites(book.value.id)
  if (result.success) {
    isFavorite.value = !isFavorite.value
    showNotification(result.message)
  }
}

const checkFavorite = () => {
  if (book.value) isFavorite.value = favoritesStore.isBookFavorite(book.value.id)
}

const submitReview = async () => {
  if (!book.value || reviewForm.value.rating === 0) {
    showNotification('Поставьте оценку', 'error')
    return
  }
  submittingReview.value = true
  try {
    const data = {
      rating: reviewForm.value.rating,
      title: reviewForm.value.title,
      comment: reviewForm.value.comment
    }
    let result
    if (userReview.value && !userReview.value.is_approved) {
      result = await reviewApi.updateReview(userReview.value.id, data)
    } else {
      result = await reviewApi.createReview(book.value.id, data)
    }
    if (result.data.success) {
      showNotification(userReview.value ? 'Отзыв обновлен' : 'Отзыв отправлен на модерацию')
      reviewForm.value = { rating: 0, title: '', comment: '' }
      await loadReviews()
      await loadReviewPermission()
    }
  } catch (err) {
    showNotification('Не удалось отправить отзыв', 'error')
  } finally {
    submittingReview.value = false
  }
}

const cancelEdit = () => {
  if (userReview.value) {
    reviewForm.value = {
      rating: userReview.value.rating,
      title: userReview.value.title || '',
      comment: userReview.value.comment || ''
    }
  } else {
    reviewForm.value = { rating: 0, title: '', comment: '' }
  }
}

const goToProduct = (similarBook) => {
  if (similarBook.slug) router.push(`/product/${similarBook.slug}`)
  else router.push(`/product/${similarBook.id}`)
}

onMounted(() => {
  loadBook()
  if (!cartStore.initialized) cartStore.loadCart()
  if (!favoritesStore.initialized) favoritesStore.loadFavorites()
})

watch(book, async () => {
  if (book.value) {
    checkFavorite()
    await loadReviews()
    await loadReviewPermission()
  }
})

watch(() => favoritesStore.favoriteBookIds, () => checkFavorite(), { deep: true })
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(100%);
}
</style>