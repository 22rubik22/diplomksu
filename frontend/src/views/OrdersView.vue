<template>
  <div class="min-h-screen bg-[#faf8f5]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <!-- Шапка страницы -->
      <div class="flex items-center justify-between mb-8 pb-4 border-b border-black/5">
        <div>
          <p class="text-[11px] tracking-[0.3em] uppercase text-[#c8a87c] font-light">Order history</p>
          <h1 class="text-2xl sm:text-3xl font-light text-[#2c2c2c] mt-1">Мои заказы</h1>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-[#8b7355]">
          <span class="w-2 h-2 rounded-full bg-[#c8a87c]"></span>
          <span>{{ total }} заказов</span>
        </div>
      </div>

      <!-- Состояние загрузки -->
      <div v-if="loading" class="bg-white rounded-2xl p-12 shadow-sm border border-[#e8e0d8] text-center">
        <div class="w-8 h-8 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
        <p class="mt-4 text-[#8b7355] text-sm">Загрузка заказов...</p>
      </div>

      <!-- Ошибка -->
      <div v-else-if="error" class="bg-white rounded-2xl p-12 shadow-sm border border-[#e8e0d8] text-center">
        <i class="fas fa-exclamation-circle text-3xl text-red-400 mb-4"></i>
        <p class="text-red-500 text-sm">{{ error }}</p>
        <button @click="loadOrders" class="mt-4 text-[#c8a87c] hover:underline text-sm">
          Попробовать снова
        </button>
      </div>

      <!-- Фильтры и заказы -->
      <div v-else>
        <!-- Фильтры -->
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#e8e0d8] mb-6">
          <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
              <i class="fas fa-filter text-[#c8a87c] text-sm"></i>
              <span class="text-xs text-[#8b7355]">Фильтр:</span>
            </div>
            
            <div class="flex flex-wrap gap-2">
              <button
                v-for="filter in filters"
                :key="filter.value"
                @click="statusFilter = filter.value; currentPage = 1; loadOrders()"
                class="px-4 py-1.5 rounded-full text-xs transition-all"
                :class="statusFilter === filter.value 
                  ? 'bg-[#c8a87c] text-white' 
                  : 'bg-[#faf8f5] text-[#8b7355] hover:bg-[#e8e0d8]'"
              >
                {{ filter.label }}
              </button>
            </div>
            
            <div class="flex-1"></div>
            
            <div class="flex items-center gap-2">
              <i class="fas fa-sort-amount-down text-[#c8a87c] text-sm"></i>
              <select 
                v-model="sortOrder"
                @change="currentPage = 1; loadOrders()"
                class="px-3 py-1.5 text-sm border border-[#e8e0d8] rounded-lg bg-white focus:outline-none focus:border-[#c8a87c]"
              >
                <option value="desc">Сначала новые</option>
                <option value="asc">Сначала старые</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Нет заказов -->
        <div v-if="!orders.length" class="bg-white rounded-2xl p-12 shadow-sm border border-[#e8e0d8] text-center">
          <i class="fas fa-shopping-bag text-4xl text-[#e8e0d8] mb-4"></i>
          <p class="text-[#8b7355] text-base">У вас пока нет заказов</p>
          <router-link to="/catalog" class="inline-block mt-4 text-sm text-[#c8a87c] hover:underline">
            Перейти в каталог
          </router-link>
        </div>

        <!-- Список заказов -->
        <div v-else class="space-y-4">
          <div 
            v-for="order in orders" 
            :key="order.id"
            class="bg-white rounded-2xl p-5 shadow-sm border border-[#e8e0d8] hover:shadow-md transition-all"
          >
            <!-- Шапка заказа -->
            <div class="flex flex-wrap justify-between items-start gap-3 pb-4 mb-4 border-b border-[#e8e0d8]">
              <div>
                <div class="flex items-center gap-3 flex-wrap">
                  <span class="font-medium text-[#2c2c2c] text-lg">Заказ #{{ order.order_number || order.id }}</span>
                  <span 
                    class="px-2 py-0.5 rounded-full text-[10px]"
                    :class="orderStatusClass(order.status)"
                  >
                    {{ orderStatusText(order.status) }}
                  </span>
                </div>
                <p class="text-xs text-[#8b7355] mt-1">
                  <i class="far fa-calendar-alt mr-1"></i>
                  {{ formatDate(order.created_at) }}
                </p>
              </div>
              
              <div class="text-right">
                <p class="text-xl font-light text-[#2c2c2c]">{{ formatPrice(getOrderTotal(order)) }} ₽</p>
                <p class="text-xs text-[#8b7355]">{{ getItemsCount(order) }} товара</p>
              </div>
            </div>

            <!-- Товары -->
            <div class="mb-4">
              <div class="flex flex-wrap gap-3">
                <div 
                  v-for="(item, idx) in getOrderItems(order).slice(0, 3)" 
                  :key="idx"
                  class="flex items-center gap-2 text-sm"
                >
                  <div class="w-10 h-12 bg-[#faf8f5] rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-[#c8a87c]/40 text-sm"></i>
                  </div>
                  <div>
                    <p class="text-[#2c2c2c] text-sm line-clamp-1 max-w-[150px]">{{ item.book_title }}</p>
                    <p class="text-[10px] text-[#8b7355]">{{ item.quantity }} шт × {{ formatPrice(item.price) }} ₽</p>
                  </div>
                </div>
                <div 
                  v-if="getOrderItems(order).length > 3"
                  class="flex items-center justify-center w-10 h-12 bg-[#faf8f5] rounded-lg text-xs text-[#8b7355]"
                >
                  +{{ getOrderItems(order).length - 3 }}
                </div>
              </div>
            </div>

            <!-- Бонусы -->
            <div v-if="order.bonus_used > 0 || order.bonus_earned > 0" class="flex flex-wrap gap-4 text-xs mb-4 pb-3 border-b border-[#e8e0d8]">
              <span v-if="order.bonus_used > 0" class="text-[#c8a87c]">
                <i class="fas fa-minus-circle mr-1"></i>
                Списано: {{ formatPrice(order.bonus_used) }}
              </span>
              <span v-if="order.bonus_earned > 0" class="text-green-600">
                <i class="fas fa-plus-circle mr-1"></i>
                Начислено: {{ formatPrice(order.bonus_earned) }}
              </span>
            </div>

            <!-- Действия -->
            <div class="flex flex-wrap gap-4">
              <button 
                @click="viewOrderDetails(order.id)"
                class="text-sm text-[#c8a87c] hover:underline"
              >
                Подробнее
              </button>
              <button 
                v-if="canCancelOrder(order.status)"
                @click="cancelOrder(order.id, order.order_number || order.id)"
                class="text-sm text-red-500 hover:underline"
              >
                Отменить
              </button>
              <button 
                v-if="order.status === 'delivered'"
                @click="repeatOrder(order.id)"
                class="text-sm text-[#c8a87c] hover:underline"
              >
                Повторить заказ
              </button>
            </div>
          </div>
        </div>

        <!-- Пагинация -->
        <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-8">
          <button 
            @click="changePage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="w-9 h-9 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <i class="fas fa-chevron-left text-xs"></i>
          </button>
          
          <button 
            v-for="page in visiblePages" 
            :key="page"
            @click="changePage(page)"
            class="w-9 h-9 rounded-xl border transition-all"
            :class="currentPage === page 
              ? 'bg-[#c8a87c] text-white border-[#c8a87c]' 
              : 'border-[#e8e0d8] bg-white hover:bg-[#faf8f5]'"
          >
            {{ page }}
          </button>
          
          <button 
            @click="changePage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="w-9 h-9 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <i class="fas fa-chevron-right text-xs"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api/axios'
import { useToast } from '@/composables/useToast'

const router = useRouter()
const { success, error: showError } = useToast()

const loading = ref(false)
const error = ref(null)
const orders = ref([])
const statusFilter = ref('')
const sortOrder = ref('desc')
const currentPage = ref(1)
const total = ref(0)
const perPage = ref(10)

const filters = [
  { value: '', label: 'Все' },
  { value: 'new', label: 'Новые' },
  { value: 'processing', label: 'В обработке' },
  { value: 'shipped', label: 'Отправлены' },
  { value: 'delivered', label: 'Доставлены' },
  { value: 'cancelled', label: 'Отменены' }
]

const totalPages = computed(() => Math.ceil(total.value / perPage.value))

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const getItemsCount = (order) => {
  if (order.items_count !== undefined) return order.items_count
  if (order.total_items !== undefined) return order.total_items
  if (order.items && Array.isArray(order.items)) return order.items.length
  if (order.order_items && Array.isArray(order.order_items)) return order.order_items.length
  return 0
}

const getOrderTotal = (order) => {
  if (order.total_amount !== undefined) return order.total_amount
  if (order.total !== undefined) return order.total
  if (order.amount !== undefined) return order.amount
  return 0
}

const getOrderItems = (order) => {
  if (order.items && Array.isArray(order.items)) return order.items
  if (order.order_items && Array.isArray(order.order_items)) return order.order_items
  return []
}

const orderStatusClass = (status) => {
  const classes = {
    'new': 'bg-blue-50 text-blue-600',
    'pending': 'bg-orange-50 text-orange-600',
    'processing': 'bg-yellow-50 text-yellow-600',
    'shipped': 'bg-purple-50 text-purple-600',
    'delivered': 'bg-green-50 text-green-600',
    'cancelled': 'bg-red-50 text-red-600',
    'refunded': 'bg-gray-50 text-gray-600'
  }
  return classes[status] || 'bg-gray-50 text-gray-600'
}

const orderStatusText = (status) => {
  const texts = {
    'new': 'Новый',
    'pending': 'Ожидает оплаты',
    'processing': 'В обработке',
    'shipped': 'Отправлен',
    'delivered': 'Доставлен',
    'cancelled': 'Отменён',
    'refunded': 'Возвращён'
  }
  return texts[status] || status
}

const canCancelOrder = (status) => {
  return status === 'new' || status === 'pending'
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const formatPrice = (price) => {
  if (!price && price !== 0) return '0'
  return new Intl.NumberFormat('ru-RU').format(price)
}

const loadOrders = async () => {
  loading.value = true
  error.value = null
  
  try {
    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      sort: sortOrder.value === 'desc' ? 'desc' : 'asc'
    }
    
    if (statusFilter.value) {
      params.status = statusFilter.value
    }
    
    const response = await api.get('/orders', { params })
    
    if (response.data.success) {
      orders.value = response.data.data.data
      total.value = response.data.data.total
    } else {
      error.value = response.data.message || 'Ошибка загрузки заказов'
    }
  } catch (err) {
    console.error('Ошибка загрузки заказов:', err)
    error.value = err.response?.data?.message || 'Не удалось загрузить заказы'
  } finally {
    loading.value = false
  }
}

const cancelOrder = async (orderId, orderNumber) => {
  if (!confirm(`Отменить заказ #${orderNumber}?`)) return
  
  try {
    const response = await api.post(`/orders/${orderId}/cancel`)
    if (response.data.success) {
      success('Заказ отменён')
      await loadOrders()
    } else {
      showError(response.data.message || 'Ошибка при отмене')
    }
  } catch (err) {
    showError('Не удалось отменить заказ')
  }
}

const repeatOrder = async (orderId) => {
  success('Заказ добавлен в корзину')
}

const changePage = (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  loadOrders()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const viewOrderDetails = (orderId) => {
  router.push(`/orders/${orderId}`)
}

onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>