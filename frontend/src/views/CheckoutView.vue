<template>
  <div class="min-h-screen bg-[#faf8f5]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <!-- Шапка страницы -->
      <div class="flex items-center justify-between mb-8 pb-4 border-b border-black/5">
        <div>
          <p class="text-[11px] tracking-[0.3em] uppercase text-[#c8a87c] font-light">Checkout</p>
          <h1 class="text-2xl sm:text-3xl font-light text-[#2c2c2c] mt-1">Оформление заказа</h1>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-[#8b7355]">
          <span class="w-2 h-2 rounded-full bg-[#c8a87c]"></span>
          <span>Безопасная оплата</span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Левая колонка: форма (2/3) -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Карточка адреса -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
            <div class="flex items-center gap-3 mb-5">
              <div class="w-8 h-8 rounded-full bg-[#c8a87c]/10 flex items-center justify-center">
                <i class="fas fa-map-marker-alt text-[#c8a87c] text-sm"></i>
              </div>
              <h3 class="text-[#2c2c2c] font-medium">Адрес доставки</h3>
            </div>
            
            <form @submit.prevent="submitOrder">
              <div class="space-y-4">
                <YandexAddress 
                  v-model="form.address"
                  :error="addressError"
                  @coordinates="handleCoordinates"
                />
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs text-[#8b7355] mb-1.5">Дата доставки</label>
                    <input 
                      v-model="form.deliveryDate" 
                      type="date" 
                      class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
                      :min="minDeliveryDate"
                    >
                  </div>
                  
                  <div>
                    <label class="block text-xs text-[#8b7355] mb-1.5">Время доставки</label>
                    <select 
                      v-model="form.deliveryTime"
                      class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
                    >
                      <option value="" disabled>Выберите время</option>
                      <option v-for="time in deliveryTimeSlots" :key="time" :value="time">
                        {{ time }}
                      </option>
                    </select>
                    <p v-if="form.deliveryTime && !isTimeValid" class="text-xs text-red-500 mt-1">
                      Выберите доступное время доставки
                    </p>
                  </div>
                </div>
                
                <div>
                  <label class="block text-xs text-[#8b7355] mb-1.5">Комментарий</label>
                  <textarea 
                    v-model="form.comment"
                    class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all resize-none"
                    rows="2"
                    placeholder="Пожелания к доставке..."
                  ></textarea>
                </div>
              </div>
            </form>
          </div>

          <!-- Карточка доставки -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
            <div class="flex items-center gap-3 mb-5">
              <div class="w-8 h-8 rounded-full bg-[#c8a87c]/10 flex items-center justify-center">
                <i class="fas fa-truck text-[#c8a87c] text-sm"></i>
              </div>
              <h3 class="text-[#2c2c2c] font-medium">Способ доставки</h3>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <label v-for="delivery in deliveryMethods" :key="delivery.id" 
                class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all"
                :class="form.deliveryMethod === delivery.id ? 'border-[#c8a87c] bg-[#c8a87c]/5' : 'border-[#e8e0d8] hover:border-[#c8a87c]/50'"
              >
                <input 
                  type="radio" 
                  v-model="form.deliveryMethod"
                  :value="delivery.id"
                  class="w-4 h-4 text-[#c8a87c]"
                >
                <div>
                  <p class="text-sm font-medium text-[#2c2c2c]">{{ delivery.name }}</p>
                  <p class="text-xs text-[#8b7355]">{{ formatPrice(delivery.price) }} ₽</p>
                </div>
              </label>
            </div>
          </div>

          <!-- Карточка оплаты -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
            <div class="flex items-center gap-3 mb-5">
              <div class="w-8 h-8 rounded-full bg-[#c8a87c]/10 flex items-center justify-center">
                <i class="fas fa-credit-card text-[#c8a87c] text-sm"></i>
              </div>
              <h3 class="text-[#2c2c2c] font-medium">Способ оплаты</h3>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <label v-for="payment in paymentMethods" :key="payment.id"
                class="flex items-center gap-3 p-4 border-2 rounded-xl cursor-pointer transition-all"
                :class="form.paymentMethod === payment.id ? 'border-[#c8a87c] bg-[#c8a87c]/5' : 'border-[#e8e0d8] hover:border-[#c8a87c]/50'"
              >
                <input 
                  type="radio" 
                  v-model="form.paymentMethod"
                  :value="payment.id"
                  class="w-4 h-4 text-[#c8a87c]"
                >
                <div>
                  <p class="text-sm font-medium text-[#2c2c2c]">{{ payment.name }}</p>
                  <p v-if="payment.id === 'card'" class="text-xs text-[#8b7355]">Мир, Visa, Mastercard</p>
                  <p v-else class="text-xs text-[#8b7355]">При получении</p>
                </div>
              </label>
            </div>

            <!-- Бонусы -->
            <div v-if="bonusInfo && bonusInfo.current_bonus > 0" class="mt-6 pt-5 border-t border-[#e8e0d8]">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <p class="text-sm font-medium text-[#2c2c2c]">Бонусный счёт</p>
                  <p class="text-xs text-[#8b7355]">Доступно {{ formatPrice(bonusInfo.current_bonus) }} бонусов</p>
                </div>
                <div class="text-right">
                  <p class="text-xs text-[#c8a87c]">Начислим +{{ formatPrice(bonusToEarn) }}</p>
                  <p class="text-[10px] text-[#8b7355]">при оплате картой</p>
                </div>
              </div>
              
              <label class="flex items-center gap-3 cursor-pointer py-2">
                <input 
                  type="checkbox"
                  v-model="useBonus"
                  @change="onUseBonusChange"
                  class="w-4 h-4 rounded border-[#e8e0d8] text-[#c8a87c]"
                >
                <span class="text-sm text-[#2c2c2c]">Использовать бонусы</span>
                <span class="text-xs text-[#8b7355]">(доступно {{ formatPrice(bonusInfo.current_bonus) }})</span>
              </label>
              
              <div v-if="useBonus" class="mt-4">
                <div class="flex items-center gap-4">
                  <input 
                    type="number"
                    v-model.number="bonusToUse"
                    @input="onBonusInputChange"
                    min="0"
                    :max="bonusInfo.current_bonus"
                    class="flex-1 px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
                    placeholder="Сумма бонусов"
                  >
                  <span class="text-sm text-[#8b7355]">бонусов = {{ formatPrice(bonusToUse) }} ₽</span>
                </div>
                <p v-if="bonusToUse > bonusInfo.current_bonus" class="text-xs text-red-500 mt-1">
                  Недостаточно бонусов на счёте
                </p>
                <p v-if="bonusToUse > totalBeforeBonus" class="text-xs text-red-500 mt-1">
                  Сумма бонусов не может превышать стоимость заказа
                </p>
              </div>
            </div>
          </div>

          <!-- Неактивные товары -->
          <div v-if="hasInactiveItems" class="bg-amber-50 rounded-2xl p-5 border border-amber-200">
            <div class="flex gap-3">
              <i class="fas fa-clock text-amber-600 text-lg"></i>
              <div>
                <p class="text-sm font-medium text-amber-800 mb-2">Некоторые товары временно недоступны</p>
                <ul class="space-y-1">
                  <li v-for="item in inactiveItems" :key="item.id" class="text-xs text-amber-700">
                    {{ item.title }} ({{ item.quantity }} шт.)
                  </li>
                </ul>
                <p class="text-xs text-amber-600 mt-2">Они останутся в корзине, вы сможете оформить их позже</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Правая колонка: итог (1/3) -->
        <div class="lg:col-span-1">
          <div class="bg-[#2c2c2c] rounded-2xl p-6 sticky top-24 text-white">
            <h3 class="text-lg font-light mb-5">Ваш заказ</h3>
            
            <div v-if="cartStore.loading" class="text-center py-8">
              <i class="fas fa-spinner fa-spin text-2xl text-[#c8a87c]"></i>
            </div>
            
            <div v-else-if="activeItems.length" class="space-y-4 mb-5 max-h-96 overflow-y-auto">
              <div v-for="item in activeItems" :key="item.id" class="flex gap-3 pb-4 border-b border-white/10">
                <div class="w-14 h-16 bg-white/10 rounded-lg flex items-center justify-center flex-shrink-0">
                  <img 
                    v-if="item.image && item.image.trim() !== '' && !imageErrors[item.id]"
                    :src="item.image" 
                    :alt="item.title" 
                    class="w-10 h-auto object-contain"
                    @error="onImageError(item.id)"
                  >
                  <i v-else class="fas fa-shopping-bag text-white/20"></i>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-light">{{ item.title }}</p>
                  <div v-if="item.color || item.size" class="flex flex-wrap gap-2 mt-1.5">
                    <span 
                      v-if="item.color" 
                      class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-white/10 rounded-full text-[9px] text-white/60"
                    >
                      <span 
                        class="w-1.5 h-1.5 rounded-full" 
                        :style="{ backgroundColor: getColorHex(item.color) }"
                      ></span>
                      {{ getColorLabel(item.color) }}
                    </span>
                    <span 
                      v-if="item.size" 
                      class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-white/10 rounded-full text-[9px] text-white/60"
                    >
                      <i class="fas fa-arrows-alt text-[7px]"></i>
                      {{ getSizeLabel(item.size) }}
                    </span>
                  </div>
                  <p class="text-xs text-white/40 mt-1">{{ item.quantity }} шт × {{ formatPrice(item.price) }} ₽</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-light">{{ formatPrice(item.price * item.quantity) }} ₽</p>
                  <button 
                    @click="removeItem(item.id)"
                    class="text-xs text-white/30 hover:text-white/60 transition-colors mt-1"
                  >
                    Удалить
                  </button>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8">
              <i class="fas fa-shopping-bag text-3xl text-white/20 mb-2"></i>
              <p class="text-white/40 text-sm">Корзина пуста</p>
            </div>

            <div v-if="hasActiveItems" class="pt-4 space-y-3 border-t border-white/10">
              <div class="flex justify-between text-sm">
                <span class="text-white/50">Товары</span>
                <span>{{ formatPrice(cartStore.total) }} ₽</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-white/50">Доставка</span>
                <span>{{ formatPrice(deliveryPrice) }} ₽</span>
              </div>
              <div v-if="useBonus && bonusToUse > 0" class="flex justify-between text-sm text-[#c8a87c]">
                <span>Бонусы</span>
                <span>-{{ formatPrice(bonusToUse) }} ₽</span>
              </div>
              <div class="flex justify-between pt-3 text-lg font-medium">
                <span>Итого</span>
                <span>{{ formatPrice(totalToPay) }} ₽</span>
              </div>
            </div>

            <button 
              @click="submitOrder"
              class="w-full mt-6 py-3 bg-[#c8a87c] text-white rounded-xl text-sm font-medium hover:bg-[#b89a6e] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="!canSubmitOrder || submitting"
            >
              <i v-if="submitting" class="fas fa-spinner fa-spin mr-2"></i>
              {{ submitting ? 'Обработка...' : 'Подтвердить заказ' }}
            </button>
            
            <p class="text-center text-xs text-white/30 mt-4">
              <i class="fas fa-lock mr-1"></i>
              Безопасная оплата
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Модальное окно оплаты -->
    <div v-if="showPaymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" @click.self="closePaymentModal">
      <CardPaymentForm
        :amount="totalToPay"
        :confirmation-url="currentConfirmationUrl"
        :payment-id="currentPaymentId"
        @success="onPaymentSuccess"
        @cancel="closePaymentModal"
        @error="onPaymentError"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'
import YandexAddress from '@/components/ui/YandexAddress.vue'
import CardPaymentForm from '@/components/ui/CardPaymentForm.vue'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()
const { success, error } = useToast()

const imageErrors = ref({})
const submitting = ref(false)
const addressError = ref('')
const selectedCoordinates = ref(null)
const showPaymentModal = ref(false)
const currentPaymentId = ref(null)
const currentConfirmationUrl = ref(null)

const bonusInfo = ref(null)
const useBonus = ref(false)
const bonusToUse = ref(0)

const minDeliveryDate = new Date().toISOString().split('T')[0]

// Генерируем слоты времени с 10:00 до 20:00 с шагом 1 час
const deliveryTimeSlots = computed(() => {
  const slots = []
  for (let hour = 10; hour <= 20; hour++) {
    slots.push(`${hour.toString().padStart(2, '0')}:00`)
  }
  return slots
})

const form = ref({
  address: '',
  deliveryDate: null,
  deliveryTime: null,
  comment: '',
  deliveryMethod: null,
  paymentMethod: null
})

const deliveryMethods = [
  { id: 'pickup', name: 'Самовывоз', price: 0 },
  { id: 'courier', name: 'Курьером', price: 300 },
  { id: 'post', name: 'Почтой', price: 350 }
]

const paymentMethods = [
  { id: 'card', name: 'Картой онлайн' },
  { id: 'cash', name: 'Наличными' }
]

const activeItems = computed(() => cartStore.items.filter(item => item.is_active === true))
const inactiveItems = computed(() => cartStore.items.filter(item => item.is_active === false))
const hasActiveItems = computed(() => activeItems.value.length > 0)
const hasInactiveItems = computed(() => inactiveItems.value.length > 0)

const totalBeforeBonus = computed(() => {
  return cartStore.total + deliveryPrice.value
})

const canSubmitOrder = computed(() => {
  return hasActiveItems.value &&
         form.value.deliveryMethod && 
         form.value.paymentMethod && 
         form.value.address &&
         form.value.deliveryTime &&
         isTimeValid.value &&
         !submitting.value &&
         isBonusValid.value
})

const isTimeValid = computed(() => {
  if (!form.value.deliveryTime) return true
  return deliveryTimeSlots.value.includes(form.value.deliveryTime)
})

const isBonusValid = computed(() => {
  if (!useBonus.value || bonusToUse.value <= 0) return true
  if (bonusToUse.value > bonusInfo.value?.current_bonus) return false
  if (bonusToUse.value > totalBeforeBonus.value) return false
  return true
})

const deliveryPrice = computed(() => {
  return deliveryMethods.find(d => d.id === form.value.deliveryMethod)?.price || 0
})

const bonusToEarn = computed(() => Math.floor(cartStore.total * 0.05))

const totalToPay = computed(() => {
  let total = cartStore.total + deliveryPrice.value
  if (useBonus.value && bonusToUse.value > 0) {
    total = total - bonusToUse.value
  }
  return Math.max(total, 0)
})

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

const getSizeLabel = (size) => {
  const labels = {
    'XS': 'XS',
    'S': 'S',
    'M': 'M',
    'L': 'L',
    'XL': 'XL',
    'XXL': 'XXL',
    'ONE_SIZE': 'One Size',
    'FREE': 'Free Size'
  }
  return labels[size] || size
}

const loadBonusInfo = async () => {
  try {
    const response = await api.get('/bonus-info')
    if (response.data.success) {
      bonusInfo.value = response.data.data
      useBonus.value = false
      bonusToUse.value = 0
    }
  } catch (err) {
    console.error('Ошибка загрузки бонусов:', err)
  }
}

const onUseBonusChange = (event) => {
  if (event.target.checked) {
    const maxAvailable = Math.min(bonusInfo.value?.current_bonus || 0, totalBeforeBonus.value)
    bonusToUse.value = maxAvailable
  } else {
    bonusToUse.value = 0
  }
}

const onBonusInputChange = () => {
  if (bonusToUse.value < 0) {
    bonusToUse.value = 0
  }
  if (bonusToUse.value > bonusInfo.value?.current_bonus) {
    bonusToUse.value = bonusInfo.value.current_bonus
  }
  if (bonusToUse.value > totalBeforeBonus.value) {
    bonusToUse.value = totalBeforeBonus.value
  }
}

const handleCoordinates = (coords) => {
  selectedCoordinates.value = coords
}

const onImageError = (itemId) => {
  imageErrors.value[itemId] = true
}

const removeItem = async (itemId) => {
  try {
    const result = await cartStore.removeItem(itemId)
    if (result.success) {
      await loadBonusInfo()
    } else {
      error('Не удалось удалить товар')
    }
  } catch (err) {
    error('Ошибка при удалении')
  }
}

const createPayment = async () => {
  const response = await api.post('/payments/create', {
    amount: totalToPay.value,
    return_url: window.location.origin + '/orders',
    description: `Оплата заказа`
  })
  
  if (!response.data.success) {
    throw new Error(response.data.message || 'Ошибка при создании платежа')
  }
  
  currentPaymentId.value = response.data.data.payment_id
  currentConfirmationUrl.value = response.data.data.confirmation_url
  return response.data.data
}

const createOrder = async () => {
  const orderData = {
    delivery_method: form.value.deliveryMethod,
    delivery_address: form.value.address,
    delivery_date: form.value.deliveryDate,
    delivery_time: form.value.deliveryTime,
    payment_method: form.value.paymentMethod,
    comment: form.value.comment,
    coordinates: selectedCoordinates.value,
    use_bonus: useBonus.value,
    bonus_amount: bonusToUse.value,
    items: activeItems.value.map(item => ({
      cart_item_id: item.id,
      product_id: item.book_id,
      quantity: item.quantity,
      price: item.price,
      color: item.color,
      size: item.size
    }))
  }

  const response = await api.post('/orders', orderData)
  
  if (!response.data.success) {
    throw new Error(response.data.message || 'Ошибка при создании заказа')
  }
  
  for (const item of activeItems.value) {
    await cartStore.removeItem(item.id)
  }
  
  return response.data.data
}

const onPaymentSuccess = async (paymentData) => {
  showPaymentModal.value = false
  try {
    const orderResult = await createOrder()
    success('Заказ успешно оплачен!')
    router.push('/account?tab=orders')
  } catch (err) {
    error(err.message || 'Оплата прошла, но не удалось создать заказ')
  }
}

const onPaymentError = (errorMessage) => {
  error(`Ошибка оплаты: ${errorMessage}`)
  closePaymentModal()
}

const closePaymentModal = () => {
  showPaymentModal.value = false
  currentPaymentId.value = null
  currentConfirmationUrl.value = null
}

const submitOrder = async () => {
  if (!hasActiveItems.value) {
    error('Нет доступных товаров для заказа')
    return
  }
  if (!form.value.deliveryMethod) {
    error('Выберите способ доставки')
    return
  }
  if (!form.value.paymentMethod) {
    error('Выберите способ оплаты')
    return
  }
  if (!form.value.address) {
    error('Введите адрес доставки')
    return
  }
  if (!form.value.deliveryTime) {
    error('Выберите время доставки')
    return
  }
  if (!isTimeValid.value) {
    error('Выберите доступное время доставки')
    return
  }
  if (!isBonusValid.value) {
    error('Некорректная сумма бонусов')
    return
  }

  submitting.value = true
  
  try {
    if (form.value.paymentMethod === 'card') {
      await createPayment()
      showPaymentModal.value = true
    } else {
      await createOrder()
      success('Заказ успешно оформлен!')
      router.push('/account?tab=orders')
    }
  } catch (err) {
    error(err.message || 'Не удалось создать заказ')
  } finally {
    submitting.value = false
  }
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0'
  return new Intl.NumberFormat('ru-RU').format(price)
}

const loadUserAddress = () => {
  const city = authStore.userCity
  const addressLine = authStore.userAddress
  
  if (city || addressLine) {
    const addressParts = []
    if (city) addressParts.push(city)
    if (addressLine) addressParts.push(addressLine)
    form.value.address = addressParts.join(', ')
  }
}

onMounted(async () => {
  await cartStore.loadCart()
  await loadBonusInfo()
  loadUserAddress()
})
</script>