<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Hero секция -->
    <div class="text-center mb-12">
      <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-3 font-light">Shopping bag</p>
      <h1 class="text-3xl sm:text-4xl font-light text-black tracking-tight">Корзина</h1>
      <div class="w-12 h-px bg-black/20 mx-auto mt-4"></div>
    </div>

    <!-- Состояние загрузки -->
    <div v-if="cartStore.loading" class="text-center py-20">
      <div class="w-8 h-8 border border-black/20 border-t-black rounded-full animate-spin mx-auto"></div>
      <p class="mt-4 text-black/40 text-sm font-light">Загрузка...</p>
    </div>

    <!-- Ошибка -->
    <div v-else-if="cartStore.error" class="text-center py-20">
      <i class="fas fa-exclamation-circle text-3xl text-black/20 mb-4"></i>
      <p class="text-black/40 text-sm">{{ cartStore.error }}</p>
      <button @click="cartStore.loadCart" class="mt-4 text-black/60 underline text-xs font-light">
        Повторить
      </button>
    </div>

    <!-- Корзина с товарами -->
    <div v-else-if="cartStore.items.length" class="space-y-8">
      <!-- Список товаров -->
      <div class="space-y-6">
        <div 
          v-for="item in cartStore.items" 
          :key="item.id" 
          class="flex flex-col sm:flex-row gap-4 sm:gap-6 pb-6 border-b border-black/5"
        >
          <!-- Изображение -->
          <div class="w-24 h-28 sm:w-28 sm:h-32 bg-[#f8f8f8] flex items-center justify-center flex-shrink-0">
            <img 
              v-if="item.image && item.image.trim() !== '' && !imageErrors[item.id]"
              :src="item.image" 
              :alt="item.title" 
              class="w-16 h-auto object-contain"
              @error="onImageError(item.id)"
            >
            <div v-else class="text-center">
              <i class="fas fa-shopping-bag text-2xl text-black/10"></i>
            </div>
          </div>

          <!-- Информация -->
          <div class="flex-1">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-2 mb-3">
              <div>
                <h3 class="text-sm font-light text-black tracking-wide">{{ item.title }}</h3>
                <!-- Отображение выбранных опций (цвет и размер) -->
                <div v-if="item.color || item.size" class="flex flex-wrap gap-2 mt-2">
                  <span 
                    v-if="item.color" 
                    class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-[#f8f8f8] rounded-full text-[10px] text-black/60"
                  >
                    <span 
                      class="w-2 h-2 rounded-full" 
                      :style="{ backgroundColor: getColorHex(item.color) }"
                    ></span>
                    {{ getColorLabel(item.color) }}
                  </span>
                  <span 
                    v-if="item.size" 
                    class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-[#f8f8f8] rounded-full text-[10px] text-black/60"
                  >
                    <i class="fas fa-arrows-alt text-[8px]"></i>
                    {{ getSizeLabel(item.size) }}
                  </span>
                </div>
                <p v-if="item.author_name" class="text-[10px] text-black/30 mt-1 font-light">
                  {{ item.author_name }}
                </p>
              </div>
              <button 
                @click="removeItem(item.id)" 
                class="text-black/20 hover:text-black/60 transition-colors text-xs"
                :disabled="removingItemId === item.id"
              >
                <i class="fas fa-times"></i>
              </button>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-4 mt-4">
              <!-- Цена -->
              <div class="text-sm font-light text-black/60">
                {{ formatPrice(item.price) }} ₽
              </div>

              <!-- Количество -->
              <div class="flex items-center gap-3">
                <button 
                  @click="updateQuantity(item.id, item.quantity - 1)"
                  class="w-8 h-8 flex items-center justify-center border border-black/10 hover:bg-black/5 transition-colors disabled:opacity-30"
                  :disabled="item.quantity <= 1 || updatingItemId === item.id"
                >
                  <i class="fas fa-minus text-[10px]"></i>
                </button>
                <span class="w-8 text-center text-sm font-light">{{ item.quantity }}</span>
                <button 
                  @click="updateQuantity(item.id, item.quantity + 1)"
                  class="w-8 h-8 flex items-center justify-center border border-black/10 hover:bg-black/5 transition-colors disabled:opacity-30"
                  :disabled="updatingItemId === item.id"
                >
                  <i class="fas fa-plus text-[10px]"></i>
                </button>
              </div>

              <!-- Сумма -->
              <div class="text-sm font-light text-black">
                {{ formatPrice(item.price * item.quantity) }} ₽
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Действия с корзиной -->
      <div class="flex justify-between items-center pt-4">
        <button 
          @click="clearCart"
          class="text-[10px] tracking-[0.2em] uppercase text-black/30 hover:text-black/60 transition-colors font-light"
          :disabled="cartStore.loading"
        >
          Очистить корзину
        </button>
      </div>

      <!-- Итоговая сумма -->
      <div class="pt-8 border-t border-black/10">
        <div class="flex justify-between items-center mb-6">
          <span class="text-xs tracking-[0.2em] uppercase text-black/40 font-light">Итого</span>
          <span class="text-2xl font-light text-black">{{ formatPrice(cartStore.total) }} ₽</span>
        </div>
        <button 
          @click="goToCheckout" 
          class="w-full sm:w-auto px-12 py-3 bg-black text-white text-[11px] tracking-[0.2em] uppercase font-light hover:bg-black/80 transition-all disabled:opacity-30"
          :disabled="cartStore.loading || !cartStore.items.length"
        >
          Оформить заказ
        </button>
      </div>
    </div>

    <!-- Пустая корзина -->
    <div v-else class="text-center py-20">
      <i class="fas fa-shopping-bag text-5xl text-black/10 mb-4"></i>
      <p class="text-black/40 text-sm font-light mb-6">Корзина пуста</p>
      <router-link to="/catalog" class="inline-block px-8 py-3 bg-black text-white text-[10px] tracking-[0.2em] uppercase font-light hover:bg-black/80 transition-all">
        Перейти в каталог
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'

const router = useRouter()
const cartStore = useCartStore()

const updatingItemId = ref(null)
const removingItemId = ref(null)
const imageErrors = ref({})

// Функция для получения hex кода цвета
const getColorHex = (colorName) => {
  const colors = {
    'черный': '#000000',
    'белый': '#FFFFFF',
    'красный': '#FF0000',
    'синий': '#0000FF',
    'зеленый': '#008000',
    'желтый': '#FFFF00',
    'коричневый': '#8B4513',
    'бежевый': '#F5F5DC',
    'серый': '#808080',
    'розовый': '#FFC0CB',
    'фиолетовый': '#800080',
    'оранжевый': '#FFA500',
    'голубой': '#87CEEB',
    'бордовый': '#800000',
    'хаки': '#C3B091'
  }
  return colors[colorName] || '#c8a87c'
}

// Функция для получения читаемого названия цвета
const getColorLabel = (color) => {
  const labels = {
    'черный': 'Черный',
    'белый': 'Белый',
    'красный': 'Красный',
    'синий': 'Синий',
    'зеленый': 'Зеленый',
    'желтый': 'Желтый',
    'коричневый': 'Коричневый',
    'бежевый': 'Бежевый',
    'серый': 'Серый',
    'розовый': 'Розовый',
    'фиолетовый': 'Фиолетовый',
    'оранжевый': 'Оранжевый',
    'голубой': 'Голубой',
    'бордовый': 'Бордовый',
    'хаки': 'Хаки'
  }
  return labels[color] || color
}

// Функция для получения читаемого названия размера
const getSizeLabel = (size) => {
  const labels = {
    'XS': 'XS (Extra Small)',
    'S': 'S (Small)',
    'M': 'M (Medium)',
    'L': 'L (Large)',
    'XL': 'XL (Extra Large)',
    'XXL': 'XXL (Double Extra Large)',
    'XXXL': 'XXXL (Triple Extra Large)',
    'ONE_SIZE': 'One Size',
    'FREE': 'Free Size'
  }
  return labels[size] || size
}

const onImageError = (itemId) => {
  imageErrors.value[itemId] = true
}

const updateQuantity = async (itemId, newQuantity) => {
  updatingItemId.value = itemId
  const result = await cartStore.updateQuantity(itemId, newQuantity)
  updatingItemId.value = null
  
  if (!result.success && result.message) {
    alert(result.message)
  }
}

const removeItem = async (itemId) => {
  removingItemId.value = itemId
  await cartStore.removeItem(itemId)
  removingItemId.value = null
}

const clearCart = async () => {
  if (confirm('Очистить корзину?')) {
    await cartStore.clearCart()
  }
}

const goToCheckout = () => {
  if (!cartStore.items.length) return
  router.push('/checkout')
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0'
  return new Intl.NumberFormat('ru-RU').format(price)
}

onMounted(() => {
  cartStore.loadCart()
})
</script>