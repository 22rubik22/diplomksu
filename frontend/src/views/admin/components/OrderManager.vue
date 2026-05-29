<template>
  <div>
    <!-- Заголовок секции -->
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
          <i class="fas fa-shopping-cart text-[#c8a87c] text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-light text-[#2c2c2c]">Управление заказами</h3>
          <p class="text-xs text-[#8b7355] mt-0.5">Просмотр и изменение статусов заказов</p>
        </div>
      </div>
      <div class="flex gap-3">
        <button 
          @click="exportOrders"
          :disabled="exportLoading"
          class="px-4 py-2 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all flex items-center gap-2 disabled:opacity-50"
        >
          <i v-if="!exportLoading" class="fas fa-file-excel text-sm"></i>
          <i v-else class="fas fa-spinner fa-spin text-sm"></i>
          <span>Экспорт</span>
        </button>
        
        <button 
          @click="exportDetailedOrders"
          :disabled="exportDetailedLoading"
          class="px-4 py-2 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all flex items-center gap-2 shadow-sm disabled:opacity-50"
        >
          <i v-if="!exportDetailedLoading" class="fas fa-chart-line text-sm"></i>
          <i v-else class="fas fa-spinner fa-spin text-sm"></i>
          <span>Детальный отчёт</span>
        </button>
      </div>
    </div>

    <!-- Фильтры -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#8b7355] text-sm"></i>
        <input 
          v-model="filters.search"
          type="text"
          placeholder="Поиск по номеру или имени..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
          @input="searchOrders"
        >
      </div>
      
      <select v-model="filters.status" class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer" @change="loadOrders">
        <option :value="null">Все статусы</option>
        <option value="new">Новый</option>
        <option value="processing">В обработке</option>
        <option value="shipped">Отправлен</option>
        <option value="delivered">Доставлен</option>
        <option value="cancelled">Отменён</option>
      </select>
      
      <select v-model="filters.payment_status" class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer" @change="loadOrders">
        <option :value="null">Все статусы оплаты</option>
        <option value="pending">Ожидает оплаты</option>
        <option value="paid">Оплачен</option>
        <option value="failed">Ошибка оплаты</option>
      </select>

      <input 
        type="date"
        v-model="filters.date_from"
        @change="loadOrders"
        class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
        placeholder="Дата с"
      >
      
      <input 
        type="date"
        v-model="filters.date_to"
        @change="loadOrders"
        class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
        placeholder="Дата по"
      >
    </div>

    <!-- Таблица заказов -->
    <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-[#e8e0d8]">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b border-[#e8e0d8] bg-[#faf8f5]">
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">№ заказа</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Дата</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Клиент</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Сумма</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Статус</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Оплата</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && orders.length === 0">
            <td colspan="7" class="p-8 text-center">
              <div class="w-6 h-6 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p class="mt-3 text-[#8b7355] text-sm">Загрузка...</p>
            </td>
          </tr>
          <tr v-else-if="orders.length === 0">
            <td colspan="7" class="p-8 text-center">
              <i class="fas fa-shopping-cart text-3xl text-[#e8e0d8] mb-2"></i>
              <p class="text-[#8b7355] text-sm">Заказы не найдены</p>
            </td>
          </tr>
          <tr v-for="order in orders" :key="order.id" class="border-b border-[#e8e0d8] hover:bg-[#faf8f5] transition-colors">
            <td class="p-4 font-mono text-[#c8a87c] text-sm font-medium">{{ order.order_number }}</td>
            <td class="p-4 text-[#8b7355] text-sm">{{ formatDate(order.created_at) }}</td>
            <td class="p-4">
              <div class="text-[#2c2c2c] text-sm font-medium">{{ order.customer_name }}</div>
              <div class="text-[10px] text-[#8b7355]">{{ order.customer_email }}</div>
            </td>
            <td class="p-4 font-medium text-[#2c2c2c] text-sm">{{ formatPrice(order.total_amount) }} ₽</td>
            <td class="p-4">
              <select 
                :value="order.status"
                @change="updateStatus(order, $event.target.value)"
                class="px-2 py-1 rounded-full text-[10px] font-medium cursor-pointer border-0"
                :class="getStatusClass(order.status)"
              >
                <option value="new">Новый</option>
                <option value="processing">В обработке</option>
                <option value="shipped">Отправлен</option>
                <option value="delivered">Доставлен</option>
                <option value="cancelled">Отменён</option>
              </select>
             </td>
            <td class="p-4">
              <span 
                class="px-2 py-1 rounded-full text-[10px] font-medium"
                :class="getPaymentStatusClass(order.payment_status)"
              >
                {{ getPaymentStatusText(order.payment_status) }}
              </span>
             </td>
            <td class="p-4">
              <button 
                @click="openModal(order)" 
                class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] hover:text-[#c8a87c] transition-all"
                title="Просмотр"
              >
                <i class="fas fa-eye text-xs"></i>
              </button>
             </td>
           </tr>
        </tbody>
       </table>
      
      <!-- Пагинация -->
      <div v-if="pagination.last_page > 1" class="flex justify-between items-center px-6 py-4 border-t border-[#e8e0d8]">
        <div class="text-xs text-[#8b7355]">
          {{ orders.length }} из {{ pagination.total }}
        </div>
        <div class="flex gap-2">
          <button 
            @click="loadOrders(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="w-8 h-8 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <i class="fas fa-chevron-left text-xs"></i>
          </button>
          <span class="px-3 py-1.5 rounded-xl bg-[#c8a87c] text-white text-sm">
            {{ pagination.current_page }}
          </span>
          <button 
            @click="loadOrders(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="w-8 h-8 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <i class="fas fa-chevron-right text-xs"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Модальное окно деталей заказа -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-2xl w-full max-w-3xl shadow-xl transform transition-all animate-modal-slide max-h-[90vh] overflow-y-auto">
        <!-- Заголовок -->
        <div class="border-b border-[#e8e0d8] px-6 py-4 sticky top-0 bg-white z-10">
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
                <i class="fas fa-shopping-cart text-[#c8a87c] text-lg"></i>
              </div>
              <div>
                <h3 class="text-xl font-light text-[#2c2c2c]">Заказ №{{ selectedOrder?.order_number }}</h3>
                <p class="text-xs text-[#8b7355] mt-0.5">от {{ formatDate(selectedOrder?.created_at) }}</p>
              </div>
            </div>
            <button @click="closeModal" class="text-[#8b7355] hover:text-[#2c2c2c] transition-colors">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>
        
        <div class="p-6" v-if="selectedOrder">
          <!-- Информация о клиенте -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            <div class="p-4 rounded-xl bg-[#faf8f5]">
              <h4 class="text-sm font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
                <i class="fas fa-user text-[#c8a87c] text-xs"></i>
                Клиент
              </h4>
              <div class="space-y-2 text-sm">
                <p><span class="text-[#8b7355] text-xs">Имя:</span> <span class="text-[#2c2c2c] ml-2">{{ selectedOrder.customer_name }}</span></p>
                <p><span class="text-[#8b7355] text-xs">Email:</span> <span class="text-[#2c2c2c] ml-2">{{ selectedOrder.customer_email }}</span></p>
                <p><span class="text-[#8b7355] text-xs">Телефон:</span> <span class="text-[#2c2c2c] ml-2">{{ selectedOrder.customer_phone || '—' }}</span></p>
              </div>
            </div>
            
            <div class="p-4 rounded-xl bg-[#faf8f5]">
              <h4 class="text-sm font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
                <i class="fas fa-truck text-[#c8a87c] text-xs"></i>
                Доставка
              </h4>
              <div class="space-y-2 text-sm">
                <p><span class="text-[#8b7355] text-xs">Способ:</span> <span class="text-[#2c2c2c] ml-2">{{ getDeliveryMethodName(selectedOrder.delivery_method) }}</span></p>
                <p><span class="text-[#8b7355] text-xs">Адрес:</span> <span class="text-[#2c2c2c] ml-2">{{ selectedOrder.delivery_address || '—' }}</span></p>
                <p><span class="text-[#8b7355] text-xs">Стоимость:</span> <span class="text-[#2c2c2c] ml-2">{{ formatPrice(selectedOrder.delivery_price) }} ₽</span></p>
              </div>
            </div>
          </div>
          
          <!-- Товары в заказе -->
          <div class="mb-6">
            <h4 class="text-sm font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
              <i class="fas fa-box text-[#c8a87c] text-xs"></i>
              Товары
            </h4>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-[#faf8f5]">
                  <tr>
                    <th class="text-left p-3 text-[#8b7355] text-xs font-medium">Название</th>
                    <th class="text-center p-3 text-[#8b7355] text-xs font-medium">Кол-во</th>
                    <th class="text-right p-3 text-[#8b7355] text-xs font-medium">Цена</th>
                    <th class="text-right p-3 text-[#8b7355] text-xs font-medium">Сумма</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in selectedOrder.items" :key="item.id" class="border-b border-[#e8e0d8]">
                    <td class="p-3 text-[#2c2c2c] text-sm">{{ item.book_title }}</td>
                    <td class="p-3 text-center text-[#8b7355] text-sm">{{ item.quantity }}</td>
                    <td class="p-3 text-right text-[#8b7355] text-sm">{{ formatPrice(item.price) }} ₽</td>
                    <td class="p-3 text-right font-medium text-[#2c2c2c] text-sm">{{ formatPrice(item.total) }} ₽</td>
                  </tr>
                </tbody>
                <tfoot class="bg-[#faf8f5]">
                  <tr>
                    <td colspan="3" class="p-3 text-right font-medium text-[#2c2c2c] text-sm">Итого:</td>
                    <td class="p-3 text-right font-bold text-[#c8a87c] text-lg">{{ formatPrice(selectedOrder.total_amount) }} ₽</td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
          
          <!-- Комментарий -->
          <div v-if="selectedOrder.comment" class="p-4 rounded-xl bg-[#faf8f5]">
            <h4 class="text-sm font-medium text-[#2c2c2c] mb-2 flex items-center gap-2">
              <i class="fas fa-comment text-[#c8a87c] text-xs"></i>
              Комментарий
            </h4>
            <p class="text-sm text-[#8b7355]">{{ selectedOrder.comment }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { orderApi } from '@/api/orders'
import { useToast } from '@/composables/useToast'

const { success, error } = useToast()

const orders = ref([])
const loading = ref(false)
const showModal = ref(false)
const selectedOrder = ref(null)
const exportLoading = ref(false)
const exportDetailedLoading = ref(false)

const filters = ref({
  search: '',
  status: null,
  payment_status: null,
  date_from: '',
  date_to: ''
})

const searchTimeout = ref(null)
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

const formatPrice = (price) => {
  return new Intl.NumberFormat('ru-RU').format(price)
}

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    new: 'bg-blue-50 text-blue-600',
    processing: 'bg-yellow-50 text-yellow-600',
    shipped: 'bg-purple-50 text-purple-600',
    delivered: 'bg-green-50 text-green-600',
    cancelled: 'bg-red-50 text-red-600'
  }
  return classes[status] || 'bg-gray-50 text-gray-600'
}

const getPaymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-50 text-yellow-600',
    paid: 'bg-green-50 text-green-600',
    failed: 'bg-red-50 text-red-600'
  }
  return classes[status] || 'bg-gray-50 text-gray-600'
}

const getPaymentStatusText = (status) => {
  const texts = {
    pending: 'Ожидает оплаты',
    paid: 'Оплачен',
    failed: 'Ошибка оплаты'
  }
  return texts[status] || status
}

const getDeliveryMethodName = (method) => {
  const methods = {
    pickup: 'Самовывоз',
    courier: 'Курьер',
    post: 'Почта'
  }
  return methods[method] || method
}

const loadOrders = async (page = 1) => {
  loading.value = true
  try {
    const params = { page, per_page: 20, ...filters.value }
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === '' || params[key] === undefined) delete params[key]
    })
    
    const response = await orderApi.getAllOrders(params)
    if (response.data.success) {
      orders.value = response.data.data.data
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total
      }
    }
  } catch (err) {
    console.error(err)
    error('Ошибка при загрузке заказов')
  } finally {
    loading.value = false
  }
}

const searchOrders = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => loadOrders(), 500)
}

const updateStatus = async (order, newStatus) => {
  try {
    const response = await orderApi.updateOrderStatus(order.id, newStatus)
    if (response.data.success) {
      order.status = newStatus
      success(`Статус заказа №${order.order_number} изменён`)
    }
  } catch (err) {
    error('Ошибка при изменении статуса')
    await loadOrders()
  }
}

const openModal = (order) => {
  selectedOrder.value = order
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedOrder.value = null
}

const exportOrders = async () => {
  exportLoading.value = true
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.date_from) params.date_from = filters.value.date_from
    if (filters.value.date_to) params.date_to = filters.value.date_to
    
    const response = await orderApi.exportToExcel(params)
    const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `orders-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    success('Экспорт выполнен')
  } catch (err) {
    error('Ошибка при экспорте')
  } finally {
    exportLoading.value = false
  }
}

const exportDetailedOrders = async () => {
  exportDetailedLoading.value = true
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.date_from) params.date_from = filters.value.date_from
    if (filters.value.date_to) params.date_to = filters.value.date_to
    
    const response = await orderApi.exportDetailedExcel(params)
    const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `orders-detailed-${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.xlsx`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
    success('Детальный отчёт экспортирован')
  } catch (err) {
    error('Ошибка при экспорте')
  } finally {
    exportDetailedLoading.value = false
  }
}

onMounted(() => loadOrders())
</script>

<style scoped>
.animate-modal-slide {
  animation: modalSlide 0.2s ease-out;
}
@keyframes modalSlide {
  from { opacity: 0; transform: scale(0.95) translateY(-10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>