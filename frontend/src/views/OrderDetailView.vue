<template>
    <div>
      <h1 class="page-title">Детали заказа</h1>
      
      <div class="bg-white rounded-xl p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
        <!-- Состояние загрузки -->
        <div v-if="loading" class="text-center py-16">
          <i class="fas fa-spinner fa-spin text-4xl text-[#7f8330]"></i>
          <p class="mt-4 text-[#6c6456]">Загрузка информации о заказе...</p>
        </div>
        
        <!-- Ошибка -->
        <div v-else-if="error" class="text-center py-16">
          <i class="fas fa-exclamation-circle text-4xl text-red-500 mb-4"></i>
          <p class="text-red-600">{{ error }}</p>
          <button @click="loadOrder" class="mt-4 text-[#7f8330] hover:underline">
            Попробовать снова
          </button>
        </div>
        
        <!-- Информация о заказе -->
        <div v-else-if="order">
          <!-- Шапка заказа -->
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 pb-6 border-b border-[#7f8330]/10">
            <div>
              <h2 class="text-2xl text-[#5e1104] font-medium mb-2">Заказ #{{ order.order_number }}</h2>
              <p class="text-[#6c6456]">Дата: {{ formatDate(order.created_at) }}</p>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-3">
              <span 
                class="px-4 py-2 rounded-full text-sm font-medium self-start sm:self-center"
                :class="orderStatusClass(order.status)"
              >
                {{ orderStatusText(order.status) }}
              </span>
              
              <button 
                v-if="canCancelOrder(order.status)"
                @click="cancelOrder"
                class="px-6 py-2 border border-red-600 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-colors"
                :disabled="cancelling"
              >
                <i v-if="cancelling" class="fas fa-spinner fa-spin mr-2"></i>
                {{ cancelling ? 'Отмена...' : 'Отменить заказ' }}
              </button>
              
              <router-link 
                to="/orders"
                class="px-6 py-2 border border-[#7f8330]/30 text-[#7f8330] rounded-lg hover:bg-[#7f8330]/10 transition-colors"
              >
                ← Все заказы
              </router-link>
            </div>
          </div>
  
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Левая колонка: товары -->
            <div class="lg:col-span-2">
              <h3 class="text-xl text-[#5e1104] mb-4">Товары</h3>
              
              <div class="space-y-4">
                <div 
                  v-for="item in order.items" 
                  :key="item.id"
                  class="flex gap-4 p-4 border border-[#7f8330]/10 rounded-lg hover:bg-[#7f8330]/3"
                >
                  <div class="w-20 h-24 bg-[#f3d8ce] rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                    <img 
                      v-if="item.book_cover_image && item.book_cover_image.trim() !== '' && !imageErrors[item.id]"
                      :src="item.book_cover_image" 
                      :alt="item.book_title" 
                      class="w-16 h-auto object-contain"
                      @error="onImageError(item.id)"
                    >
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <i class="fas fa-book-open text-2xl text-[#b59b6d]/50"></i>
                    </div>
                  </div>
                  
                  <div class="flex-1">
                    <h4 class="font-medium text-[#5e1104] mb-1">{{ item.book_title }}</h4>
                    <p v-if="item.book_author" class="text-sm text-[#6c6456] mb-2">{{ item.book_author }}</p>
                    <div class="flex justify-between items-center">
                      <p class="text-sm text-[#6c6456]">{{ item.quantity }} шт. × {{ formatPrice(item.price) }} ₽</p>
                      <p class="font-medium text-[#5e1104]">{{ formatPrice(item.total) }} ₽</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
  
            <!-- Правая колонка: информация -->
            <div class="lg:col-span-1">
              <div class="bg-[#7f8330]/5 p-6 rounded-lg space-y-6">
                <!-- Доставка -->
                <div>
                  <h4 class="text-[#5e1104] font-medium mb-3">Доставка</h4>
                  <div class="space-y-2 text-sm">
                    <p><span class="text-[#6c6456]">Способ:</span> <span class="text-[#5e1104]">{{ deliveryMethodText(order.delivery_method) }}</span></p>
                    <p><span class="text-[#6c6456]">Адрес:</span> <span class="text-[#5e1104]">{{ order.delivery_address }}</span></p>
                    <p v-if="order.delivery_date"><span class="text-[#6c6456]">Дата:</span> <span class="text-[#5e1104]">{{ formatDate(order.delivery_date) }}</span></p>
                    <p><span class="text-[#6c6456]">Стоимость:</span> <span class="text-[#5e1104]">{{ formatPrice(order.delivery_price) }} ₽</span></p>
                  </div>
                </div>
  
                <!-- Оплата -->
                <div class="pt-4 border-t border-[#7f8330]/20">
                  <h4 class="text-[#5e1104] font-medium mb-3">Оплата</h4>
                  <div class="space-y-2 text-sm">
                    <p><span class="text-[#6c6456]">Способ:</span> <span class="text-[#5e1104]">{{ paymentMethodText(order.payment_method) }}</span></p>
                    <p><span class="text-[#6c6456]">Статус:</span> 
                      <span 
                        class="px-2 py-1 rounded text-xs"
                        :class="paymentStatusClass(order.payment_status)"
                      >
                        {{ paymentStatusText(order.payment_status) }}
                      </span>
                    </p>
                  </div>
                </div>
  
                <!-- Итого -->
                <div class="pt-4 border-t border-[#7f8330]/20">
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-[#6c6456]">Товары:</span>
                      <span>{{ formatPrice(order.total_amount - order.delivery_price) }} ₽</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-[#6c6456]">Доставка:</span>
                      <span>{{ formatPrice(order.delivery_price) }} ₽</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold text-[#5e1104] pt-2 border-t border-[#7f8330]/20">
                      <span>Итого:</span>
                      <span>{{ formatPrice(order.total_amount) }} ₽</span>
                    </div>
                  </div>
                </div>
  
                <!-- Комментарий -->
                <div v-if="order.comment" class="pt-4 border-t border-[#7f8330]/20">
                  <h4 class="text-[#5e1104] font-medium mb-2">Комментарий</h4>
                  <p class="text-sm text-[#6c6456]">{{ order.comment }}</p>
                </div>
              </div>
            </div>
          </div>
  
          <!-- Информация о покупателе -->
          <div v-if="order.customer_name || order.customer_email || order.customer_phone" class="mt-8 pt-6 border-t border-[#7f8330]/10">
            <h3 class="text-xl text-[#5e1104] mb-4">Контактная информация</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
              <div v-if="order.customer_name">
                <p class="text-[#6c6456]">ФИО</p>
                <p class="font-medium text-[#5e1104]">{{ order.customer_name }}</p>
              </div>
              <div v-if="order.customer_email">
                <p class="text-[#6c6456]">Email</p>
                <p class="font-medium text-[#5e1104]">{{ order.customer_email }}</p>
              </div>
              <div v-if="order.customer_phone">
                <p class="text-[#6c6456]">Телефон</p>
                <p class="font-medium text-[#5e1104]">{{ order.customer_phone }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, onMounted } from 'vue'
  import { useRoute } from 'vue-router'
  import api from '@/api/axios'
  
  const route = useRoute()
  
  const loading = ref(false)
  const error = ref(null)
  const order = ref(null)
  const imageErrors = ref({})
  const cancelling = ref(false)
  
  const orderStatusClass = (status) => {
    const classes = {
      'new': 'bg-blue-100 text-blue-700',
      'processing': 'bg-yellow-100 text-yellow-700',
      'shipped': 'bg-purple-100 text-purple-700',
      'delivered': 'bg-green-100 text-green-700',
      'cancelled': 'bg-red-100 text-red-700',
      'pending': 'bg-orange-100 text-orange-700',
      'refunded': 'bg-gray-100 text-gray-700'
    }
    return classes[status] || 'bg-gray-100 text-gray-700'
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
      'pending': 'bg-orange-100 text-orange-700',
      'paid': 'bg-green-100 text-green-700',
      'failed': 'bg-red-100 text-red-700',
      'refunded': 'bg-gray-100 text-gray-700'
    }
    return classes[status] || 'bg-gray-100 text-gray-700'
  }
  
  const paymentStatusText = (status) => {
    const texts = {
      'pending': 'Ожидает',
      'paid': 'Оплачен',
      'failed': 'Ошибка оплаты',
      'refunded': 'Возвращён'
    }
    return texts[status] || status
  }
  
  const deliveryMethodText = (method) => {
    const methods = {
      'courier': 'Курьерская доставка',
      'pickup': 'Пункт выдачи',
      'post': 'Почта России'
    }
    return methods[method] || method
  }
  
  const paymentMethodText = (method) => {
    const methods = {
      'card': 'Банковская карта онлайн',
      'cod': 'Наложенный платеж'
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
        alert('Заказ успешно отменён')
        await loadOrder()
      } else {
        alert(response.data.message || 'Ошибка при отмене заказа')
      }
    } catch (err) {
      console.error('Ошибка при отмене заказа:', err)
      const message = err.response?.data?.message || 'Не удалось отменить заказ'
      alert(message)
    } finally {
      cancelling.value = false
    }
  }
  
  onMounted(() => {
    loadOrder()
  })
  </script>