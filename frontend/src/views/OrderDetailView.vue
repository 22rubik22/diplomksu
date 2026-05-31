<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Хлебные крошки -->
    <div class="mb-8 text-[10px] tracking-[0.2em] uppercase text-black/30">
      <span class="hover:text-black/60 cursor-pointer" @click="$router.push('/')">Главная</span>
      <span class="mx-2">/</span>
      <span class="hover:text-black/60 cursor-pointer" @click="$router.push('/account')">Личный кабинет</span>
      <span class="mx-2">/</span>
      <span class="hover:text-black/60 cursor-pointer" @click="$router.push('/account?tab=orders')">Заказы</span>
      <span class="mx-2">/</span>
      <span class="text-black/60">Заказ #{{ order?.order_number }}</span>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-[#e8e0d8] overflow-hidden">
      <!-- Состояние загрузки -->
      <div v-if="loading" class="text-center py-20">
        <div class="w-8 h-8 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-[#8b7355] text-sm font-light">Загрузка информации о заказе...</p>
      </div>
      
      <!-- Ошибка -->
      <div v-else-if="error" class="text-center py-20">
        <i class="fas fa-exclamation-circle text-4xl text-[#c8a87c]/40 mb-4"></i>
        <p class="text-[#8b7355] text-sm">{{ error }}</p>
        <button @click="loadOrder" class="mt-4 text-[#c8a87c] hover:underline text-xs">
          Попробовать снова
        </button>
      </div>
      
      <!-- Информация о заказе -->
      <div v-else-if="order">
        <!-- Шапка заказа -->
        <div class="p-6 border-b border-[#e8e0d8] bg-[#faf8f5]">
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
              <h2 class="text-xl font-light text-[#2c2c2c] mb-1">Заказ #{{ order.order_number }}</h2>
              <p class="text-[#8b7355] text-xs">Оформлен {{ formatDate(order.created_at) }}</p>
            </div>
            
            <div class="flex flex-wrap gap-3">
              <span 
                class="px-3 py-1.5 rounded-full text-[10px] font-medium tracking-wide"
                :class="orderStatusClass(order.status)"
              >
                {{ orderStatusText(order.status) }}
              </span>
              
              <button 
                v-if="canCancelOrder(order.status)"
                @click="cancelOrder"
                class="px-4 py-1.5 border border-red-400 text-red-500 rounded-lg text-xs hover:bg-red-50 transition-colors"
                :disabled="cancelling"
              >
                <i v-if="cancelling" class="fas fa-spinner fa-spin mr-1"></i>
                {{ cancelling ? 'Отмена...' : 'Отменить заказ' }}
              </button>
              
              <router-link 
                to="/account?tab=orders"
                class="px-4 py-1.5 border border-[#e8e0d8] text-[#8b7355] rounded-lg text-xs hover:bg-[#faf8f5] transition-colors"
              >
                <i class="fas fa-arrow-left mr-1"></i>
                Все заказы
              </router-link>
            </div>
          </div>
        </div>

        <div class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Левая колонка: товары -->
            <div class="lg:col-span-2">
              <h3 class="text-sm font-medium text-[#2c2c2c] mb-4 flex items-center gap-2">
                <i class="fas fa-shopping-bag text-[#c8a87c] text-xs"></i>
                Товары в заказе
              </h3>
              
              <div class="space-y-4">
                <div 
                  v-for="item in order.items" 
                  :key="item.id"
                  class="flex gap-4 p-4 border border-[#e8e0d8] rounded-xl hover:bg-[#faf8f5] transition-colors"
                >
                  <div class="w-20 h-24 bg-[#faf8f5] rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0 border border-[#e8e0d8]">
                    <img 
                      v-if="item.image && item.image.trim() !== '' && !imageErrors[item.id]"
                      :src="getFullImageUrl(item.image)" 
                      :alt="item.product_title" 
                      class="w-16 h-auto object-contain"
                      @error="onImageError(item.id)"
                    >
                    <i v-else class="fas fa-shopping-bag text-2xl text-[#c8a87c]/20"></i>
                  </div>
                  
                  <div class="flex-1">
                    <h4 class="font-medium text-[#2c2c2c] text-sm mb-1">{{ item.product_title }}</h4>
                    
                    <!-- Выбранные опции (цвет и размер) -->
                    <div v-if="item.color || item.size" class="flex flex-wrap gap-2 mb-2">
                      <span 
                        v-if="item.color" 
                        class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-[#faf8f5] rounded-full text-[9px] text-[#8b7355]"
                      >
                        <span 
                          class="w-1.5 h-1.5 rounded-full" 
                          :style="{ backgroundColor: getColorHex(item.color) }"
                        ></span>
                        {{ getColorLabel(item.color) }}
                      </span>
                      <span 
                        v-if="item.size" 
                        class="inline-flex items-center gap-1 px-1.5 py-0.5 bg-[#faf8f5] rounded-full text-[9px] text-[#8b7355]"
                      >
                        <i class="fas fa-arrows-alt text-[7px]"></i>
                        {{ getSizeLabel(item.size) }}
                      </span>
                    </div>
                    
                    <p v-if="item.product_brand" class="text-xs text-[#8b7355] mb-2">{{ item.product_brand }}</p>
                    
                    <div class="flex justify-between items-center mt-2">
                      <p class="text-xs text-[#8b7355]">{{ item.quantity }} шт. × {{ formatPrice(item.price) }} ₽</p>
                      <p class="font-medium text-[#2c2c2c] text-sm">{{ formatPrice(item.total) }} ₽</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Правая колонка: информация -->
            <div class="lg:col-span-1">
              <div class="bg-[#faf8f5] rounded-xl p-5 space-y-5">
                <!-- Доставка -->
                <div>
                  <h4 class="text-xs font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
                    <i class="fas fa-truck text-[#c8a87c] text-xs"></i>
                    Доставка
                  </h4>
                  <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                      <span class="text-[#8b7355]">Способ:</span>
                      <span class="text-[#2c2c2c]">{{ deliveryMethodText(order.delivery_method) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-[#8b7355]">Адрес:</span>
                      <span class="text-[#2c2c2c] text-right">{{ order.delivery_address }}</span>
                    </div>
                    <div v-if="order.delivery_date" class="flex justify-between">
                      <span class="text-[#8b7355]">Дата:</span>
                      <span class="text-[#2c2c2c]">{{ formatDate(order.delivery_date) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-[#8b7355]">Стоимость:</span>
                      <span class="text-[#2c2c2c]">{{ formatPrice(order.delivery_price) }} ₽</span>
                    </div>
                  </div>
                </div>

                <!-- Оплата -->
                <div class="pt-4 border-t border-[#e8e0d8]">
                  <h4 class="text-xs font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
                    <i class="fas fa-credit-card text-[#c8a87c] text-xs"></i>
                    Оплата
                  </h4>
                  <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                      <span class="text-[#8b7355]">Способ:</span>
                      <span class="text-[#2c2c2c]">{{ paymentMethodText(order.payment_method) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                      <span class="text-[#8b7355]">Статус:</span>
                      <span 
                        class="px-2 py-0.5 rounded-full text-[9px] font-medium"
                        :class="paymentStatusClass(order.payment_status)"
                      >
                        {{ paymentStatusText(order.payment_status) }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Бонусы -->
                <div v-if="order.bonus_used > 0 || order.bonus_earned > 0" class="pt-4 border-t border-[#e8e0d8]">
                  <h4 class="text-xs font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
                    <i class="fas fa-coins text-[#c8a87c] text-xs"></i>
                    Бонусы
                  </h4>
                  <div class="space-y-2 text-xs">
                    <div v-if="order.bonus_used > 0" class="flex justify-between">
                      <span class="text-[#8b7355]">Списано:</span>
                      <span class="text-red-500">-{{ formatPrice(order.bonus_used) }} ₽</span>
                    </div>
                    <div v-if="order.bonus_earned > 0" class="flex justify-between">
                      <span class="text-[#8b7355]">Начислено:</span>
                      <span class="text-green-600">+{{ formatPrice(order.bonus_earned) }} ₽</span>
                    </div>
                  </div>
                </div>

                <!-- Итого -->
                <div class="pt-4 border-t border-[#e8e0d8]">
                  <div class="space-y-2 text-xs">
                    <div class="flex justify-between">
                      <span class="text-[#8b7355]">Товары:</span>
                      <span>{{ formatPrice(order.total_amount - order.delivery_price) }} ₽</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-[#8b7355]">Доставка:</span>
                      <span>{{ formatPrice(order.delivery_price) }} ₽</span>
                    </div>
                    <div v-if="order.bonus_used > 0" class="flex justify-between text-red-500">
                      <span>Бонусы:</span>
                      <span>-{{ formatPrice(order.bonus_used) }} ₽</span>
                    </div>
                    <div class="flex justify-between pt-2 text-base font-medium text-[#2c2c2c] border-t border-[#e8e0d8]">
                      <span>Итого:</span>
                      <span>{{ formatPrice(order.total_amount) }} ₽</span>
                    </div>
                  </div>
                </div>

                <!-- Комментарий -->
                <div v-if="order.comment" class="pt-4 border-t border-[#e8e0d8]">
                  <h4 class="text-xs font-medium text-[#2c2c2c] mb-2 flex items-center gap-2">
                    <i class="fas fa-comment text-[#c8a87c] text-xs"></i>
                    Комментарий
                  </h4>
                  <p class="text-xs text-[#8b7355]">{{ order.comment }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Контактная информация -->
          <div v-if="order.customer_name || order.customer_email || order.customer_phone" class="mt-8 pt-6 border-t border-[#e8e0d8]">
            <h3 class="text-sm font-medium text-[#2c2c2c] mb-4 flex items-center gap-2">
              <i class="fas fa-user text-[#c8a87c] text-xs"></i>
              Контактная информация
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
              <div v-if="order.customer_name">
                <p class="text-[#8b7355] mb-0.5">Получатель</p>
                <p class="font-medium text-[#2c2c2c]">{{ order.customer_name }}</p>
              </div>
              <div v-if="order.customer_phone">
                <p class="text-[#8b7355] mb-0.5">Телефон</p>
                <p class="font-medium text-[#2c2c2c]">{{ order.customer_phone }}</p>
              </div>
              <div v-if="order.customer_email">
                <p class="text-[#8b7355] mb-0.5">Email</p>
                <p class="font-medium text-[#2c2c2c] break-all">{{ order.customer_email }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api/axios'
import { useToast } from '@/composables/useToast'

const route = useRoute()
const router = useRouter()
const { success, error: showError } = useToast()

const loading = ref(false)
const error = ref(null)
const order = ref(null)
const imageErrors = ref({})
const cancelling = ref(false)

// Функции для цветов и размеров
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

const getFullImageUrl = (path) => {
  if (!path) return ''
  if (path.startsWith('http')) return path
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  return `${baseUrl}${path}`
}

const orderStatusClass = (status) => {
  const classes = {
    'new': 'bg-blue-50 text-blue-600',
    'processing': 'bg-yellow-50 text-yellow-600',
    'shipped': 'bg-purple-50 text-purple-600',
    'delivered': 'bg-green-50 text-green-600',
    'cancelled': 'bg-red-50 text-red-600',
    'pending': 'bg-orange-50 text-orange-600',
    'refunded': 'bg-gray-50 text-gray-600'
  }
  return classes[status] || 'bg-gray-50 text-gray-600'
}

const orderStatusText = (status) => {
  const texts = {
    'new': 'Новый',
    'processing': 'В обработке',
    'shipped': 'Отправлен',
    'delivered': 'Доставлен',
    'cancelled': 'Отменён',
    'pending': 'Ожидает оплаты',
    'refunded': 'Возвращён'
  }
  return texts[status] || status
}

const paymentStatusClass = (status) => {
  const classes = {
    'pending': 'bg-orange-50 text-orange-600',
    'paid': 'bg-green-50 text-green-600',
    'failed': 'bg-red-50 text-red-600',
    'refunded': 'bg-gray-50 text-gray-600'
  }
  return classes[status] || 'bg-gray-50 text-gray-600'
}

const paymentStatusText = (status) => {
  const texts = {
    'pending': 'Ожидает оплаты',
    'paid': 'Оплачен',
    'failed': 'Ошибка оплаты',
    'refunded': 'Возвращён'
  }
  return texts[status] || status
}

const deliveryMethodText = (method) => {
  const methods = {
    'pickup': 'Самовывоз',
    'courier': 'Курьером',
    'post': 'Почтой'
  }
  return methods[method] || method
}

const paymentMethodText = (method) => {
  const methods = {
    'card': 'Картой онлайн',
    'cash': 'Наличными'
  }
  return methods[method] || method
}

const canCancelOrder = (status) => {
  return status === 'new' || status === 'pending'
}

const onImageError = (itemId) => {
  imageErrors.value[itemId] = true
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0'
  return new Intl.NumberFormat('ru-RU').format(price)
}

const loadOrder = async () => {
  loading.value = true
  error.value = null
  
  try {
    const orderId = route.params.id
    const response = await api.get(`/orders/${orderId}`)
    
    if (response.data.success) {
      order.value = response.data.data
    } else {
      error.value = response.data.message || 'Ошибка загрузки заказа'
    }
  } catch (err) {
    console.error('Ошибка загрузки заказа:', err)
    error.value = err.response?.data?.message || 'Не удалось загрузить заказ'
  } finally {
    loading.value = false
  }
}

const cancelOrder = async () => {
  if (!confirm(`Вы уверены, что хотите отменить заказ #${order.value.order_number}?`)) {
    return
  }
  
  cancelling.value = true
  
  try {
    const orderId = route.params.id
    const response = await api.post(`/orders/${orderId}/cancel`)
    
    if (response.data.success) {
      success('Заказ успешно отменён')
      await loadOrder()
    } else {
      showError(response.data.message || 'Ошибка при отмене заказа')
    }
  } catch (err) {
    console.error('Ошибка при отмене заказа:', err)
    const message = err.response?.data?.message || 'Не удалось отменить заказ'
    showError(message)
  } finally {
    cancelling.value = false
  }
}

onMounted(() => {
  loadOrder()
})
</script>