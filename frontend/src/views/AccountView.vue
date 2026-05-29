<template>
  <div class="min-h-screen bg-[#faf8f5]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <!-- Шапка страницы -->
      <div class="flex items-center justify-between mb-8 pb-4 border-b border-black/5">
        <div>
          <p class="text-[11px] tracking-[0.3em] uppercase text-[#c8a87c] font-light">Account</p>
          <h1 class="text-2xl sm:text-3xl font-light text-[#2c2c2c] mt-1">Личный кабинет</h1>
        </div>
        <button 
          @click="handleLogout"
          class="flex items-center gap-2 px-4 py-2 text-sm text-[#8b7355] hover:text-[#c8a87c] transition-colors"
        >
          <i class="fas fa-sign-out-alt"></i>
          <span class="hidden sm:inline">Выйти</span>
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        <!-- Боковая панель (1/4) -->
        <div class="lg:col-span-1">
          <!-- Карточка профиля -->
          <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8] text-center mb-6">
            <div class="w-24 h-24 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-[#c8a87c]/20 to-[#8b7355]/10 flex items-center justify-center overflow-hidden">
              <i class="fas fa-user text-3xl text-[#c8a87c]"></i>
            </div>
            <h3 class="text-[#2c2c2c] font-medium text-lg">{{ authStore.userName || 'Пользователь' }}</h3>
            <p class="text-sm text-[#8b7355] mt-1 break-all">{{ authStore.userEmail || 'email@example.com' }}</p>
            
            <!-- Бонусы -->
            <div class="mt-5 pt-4 border-t border-[#e8e0d8]">
              <div class="flex items-center justify-between">
                <div class="text-left">
                  <p class="text-xs text-[#8b7355]">Бонусный счёт</p>
                  <p class="text-2xl font-light text-[#c8a87c]">{{ formatPrice(userBonus) }} ₽</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-[#c8a87c]/10 flex items-center justify-center">
                  <i class="fas fa-coins text-[#c8a87c] text-lg"></i>
                </div>
              </div>
              <p class="text-[10px] text-[#8b7355] mt-2 text-left">
                Начисляем 5% бонусов при оплате картой
              </p>
            </div>
          </div>

          <!-- Навигация -->
          <nav class="bg-white rounded-2xl p-2 shadow-sm border border-[#e8e0d8]">
            <button 
              v-for="item in menuItems" 
              :key="item.key"
              @click="switchTab(item.key)"
              class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all text-sm mb-1"
              :class="currentTab === item.key ? 'bg-[#c8a87c]/10 text-[#c8a87c]' : 'text-[#8b7355] hover:bg-[#faf8f5]'"
            >
              <i :class="item.icon" class="w-5 text-center"></i>
              <span>{{ item.name }}</span>
            </button>
          </nav>
        </div>

        <!-- Основной контент (3/4) -->
        <div class="lg:col-span-3">
          <!-- Обзор -->
          <div v-if="currentTab === 'overview'" class="space-y-6">
            <!-- Статистика -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#e8e0d8]">
                <div class="flex items-center justify-between mb-3">
                  <div class="w-10 h-10 rounded-full bg-[#c8a87c]/10 flex items-center justify-center">
                    <i class="fas fa-shopping-bag text-[#c8a87c]"></i>
                  </div>
                  <span class="text-2xl font-light text-[#2c2c2c]">{{ orders?.length || 0 }}</span>
                </div>
                <p class="text-sm text-[#8b7355]">Всего заказов</p>
              </div>
              
              <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#e8e0d8]">
                <div class="flex items-center justify-between mb-3">
                  <div class="w-10 h-10 rounded-full bg-[#c8a87c]/10 flex items-center justify-center">
                    <i class="fas fa-coins text-[#c8a87c]"></i>
                  </div>
                  <span class="text-2xl font-light text-[#2c2c2c]">{{ formatPrice(userBonus) }}</span>
                </div>
                <p class="text-sm text-[#8b7355]">Бонусов доступно</p>
              </div>
              
              <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#e8e0d8]">
                <div class="flex items-center justify-between mb-3">
                  <div class="w-10 h-10 rounded-full bg-[#c8a87c]/10 flex items-center justify-center">
                    <i class="fas fa-calendar-alt text-[#c8a87c]"></i>
                  </div>
                  <span class="text-sm font-light text-[#2c2c2c]">{{ memberSince || 'Новый' }}</span>
                </div>
                <p class="text-sm text-[#8b7355]">С нами с</p>
              </div>
            </div>

            <!-- Последние заказы -->
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
              <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-light text-[#2c2c2c]">Последние заказы</h3>
                <button 
                  v-if="orders?.length > 3"
                  @click="switchTab('orders')"
                  class="text-xs text-[#c8a87c] hover:underline"
                >
                  Все заказы →
                </button>
              </div>
              
              <div v-if="ordersLoading" class="text-center py-8">
                <div class="w-6 h-6 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
              </div>
              
              <div v-else-if="recentOrders?.length" class="space-y-3">
                <div 
                  v-for="order in recentOrders" 
                  :key="order.id"
                  class="p-4 rounded-xl border border-[#e8e0d8] hover:shadow-md transition-all"
                >
                  <div class="flex flex-wrap justify-between items-start gap-2 mb-2">
                    <div>
                      <span class="font-medium text-[#2c2c2c]">Заказ #{{ order.order_number || order.id }}</span>
                      <span class="text-xs text-[#8b7355] ml-2">{{ formatDate(order.created_at) }}</span>
                    </div>
                    <span 
                      class="px-2 py-0.5 rounded-full text-[10px]"
                      :class="getStatusClass(order.status)"
                    >
                      {{ getStatusText(order.status) }}
                    </span>
                  </div>
                  <div class="text-sm text-[#8b7355] mb-3">
                    {{ getItemsCount(order) }} товара • {{ formatPrice(getOrderTotal(order)) }} ₽
                  </div>
                  <button 
                    @click="viewOrder(order.id)"
                    class="text-xs text-[#c8a87c] hover:underline"
                  >
                    Подробнее
                  </button>
                </div>
              </div>
              
              <div v-else class="text-center py-8">
                <i class="fas fa-shopping-bag text-3xl text-[#e8e0d8] mb-2"></i>
                <p class="text-[#8b7355]">У вас пока нет заказов</p>
                <router-link to="/catalog" class="inline-block mt-3 text-sm text-[#c8a87c] hover:underline">
                  Перейти в каталог
                </router-link>
              </div>
            </div>
          </div>
          
          <!-- Мои заказы -->
          <div v-else-if="currentTab === 'orders'" class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
            <h3 class="text-lg font-light text-[#2c2c2c] mb-5">Мои заказы</h3>
            
            <div v-if="ordersLoading" class="text-center py-12">
              <div class="w-8 h-8 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
            </div>
            
            <div v-else-if="orders?.length" class="space-y-4">
              <div 
                v-for="order in orders" 
                :key="order.id"
                class="p-4 rounded-xl border border-[#e8e0d8] hover:shadow-md transition-all"
              >
                <div class="flex flex-wrap justify-between items-start gap-2 mb-2">
                  <div>
                    <span class="font-medium text-[#2c2c2c]">Заказ #{{ order.order_number || order.id }}</span>
                    <span class="text-xs text-[#8b7355] ml-2">{{ formatDate(order.created_at) }}</span>
                  </div>
                  <span 
                    class="px-2 py-0.5 rounded-full text-[10px]"
                    :class="getStatusClass(order.status)"
                  >
                    {{ getStatusText(order.status) }}
                  </span>
                </div>
                
                <div class="text-sm text-[#8b7355] mb-3">
                  {{ getItemsCount(order) }} товара • {{ formatPrice(getOrderTotal(order)) }} ₽
                </div>
                
                <div v-if="order.bonus_used > 0 || order.bonus_earned > 0" class="flex flex-wrap gap-3 text-xs mb-3">
                  <span v-if="order.bonus_used > 0" class="text-[#c8a87c]">
                    <i class="fas fa-minus-circle mr-1"></i>
                    Списано: {{ formatPrice(order.bonus_used) }}
                  </span>
                  <span v-if="order.bonus_earned > 0" class="text-green-600">
                    <i class="fas fa-plus-circle mr-1"></i>
                    Начислено: {{ formatPrice(order.bonus_earned) }}
                  </span>
                </div>
                
                <div class="flex flex-wrap gap-4">
                  <button 
                    @click="viewOrder(order.id)"
                    class="text-xs text-[#c8a87c] hover:underline"
                  >
                    Подробнее
                  </button>
                  <button 
                    v-if="canCancelOrder(order.status)"
                    @click="cancelOrder(order.id, order.order_number || order.id)"
                    class="text-xs text-red-500 hover:underline"
                  >
                    Отменить
                  </button>
                  <button 
                    v-if="order.status === 'delivered'"
                    @click="repeatOrder(order.id)"
                    class="text-xs text-[#c8a87c] hover:underline"
                  >
                    Повторить заказ
                  </button>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-12">
              <i class="fas fa-shopping-bag text-4xl text-[#e8e0d8] mb-3"></i>
              <p class="text-[#8b7355]">У вас пока нет заказов</p>
              <router-link to="/catalog" class="inline-block mt-3 text-sm text-[#c8a87c] hover:underline">
                Перейти в каталог
              </router-link>
            </div>
          </div>
          
          <!-- Избранное -->
          <div v-else-if="currentTab === 'favorites'" class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
            <h3 class="text-lg font-light text-[#2c2c2c] mb-5">Избранное</h3>
            
            <div v-if="favoritesLoading" class="text-center py-12">
              <div class="w-8 h-8 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
            </div>
            
            <div v-else-if="favoritesItems.length" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
              <BookCard 
                v-for="book in favoritesItems" 
                :key="book.id"
                :book="book"
                @click="goToProduct(book)"
              />
            </div>
            
            <div v-else class="text-center py-12">
              <i class="far fa-heart text-4xl text-[#e8e0d8] mb-3"></i>
              <p class="text-[#8b7355]">В избранном пока нет товаров</p>
              <router-link to="/catalog" class="inline-block mt-3 text-sm text-[#c8a87c] hover:underline">
                Перейти в каталог
              </router-link>
            </div>
          </div>

          <!-- Настройки профиля -->
          <div v-else-if="currentTab === 'profile'" class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
            <ProfileEdit />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFavoritesStore } from '@/stores/favorites'
import { orderApi } from '@/api/orders'
import api from '@/api/axios'
import { useToast } from '@/composables/useToast'
import BookCard from '@/components/ui/BookCard.vue'
import ProfileEdit from './ProfileEdit.vue'

const router = useRouter()
const authStore = useAuthStore()
const favoritesStore = useFavoritesStore()
const { success, error } = useToast()

const currentTab = ref('overview')
const orders = ref([])
const ordersLoading = ref(false)
const favoritesLoading = ref(false)
const userBonus = ref(0)

const menuItems = [
  { key: 'overview', name: 'Обзор', icon: 'fas fa-chart-line' },
  { key: 'orders', name: 'Заказы', icon: 'fas fa-shopping-bag' },
  { key: 'favorites', name: 'Избранное', icon: 'far fa-heart' },
  { key: 'profile', name: 'Профиль', icon: 'fas fa-user-edit' }
]

const getItemsCount = (order) => {
  if (order.items_count !== undefined) return order.items_count
  if (order.total_items !== undefined) return order.total_items
  if (order.items && Array.isArray(order.items)) return order.items.length
  if (order.order_items && Array.isArray(order.order_items)) return order.order_items.length
  return 0
}

const getOrderTotal = (order) => {
  if (order.total !== undefined) return order.total
  if (order.total_amount !== undefined) return order.total_amount
  if (order.amount !== undefined) return order.amount
  return 0
}

const loadOrders = async () => {
  ordersLoading.value = true
  try {
    const response = await orderApi.getUserOrders({ per_page: 50 })
    if (response.data.success) {
      let ordersData = response.data.data
      if (ordersData.data) ordersData = ordersData.data
      else if (!Array.isArray(ordersData)) ordersData = []
      orders.value = ordersData
    } else {
      orders.value = []
    }
  } catch (err) {
    console.error('Ошибка загрузки заказов:', err)
    orders.value = []
  } finally {
    ordersLoading.value = false
  }
}

const loadUserBonus = async () => {
  try {
    const response = await api.get('/bonus-info')
    if (response.data.success) {
      userBonus.value = response.data.data.current_bonus
    }
  } catch (err) {
    console.error('Ошибка загрузки бонусов:', err)
  }
}

const loadFavorites = async () => {
  favoritesLoading.value = true
  try {
    await favoritesStore.loadFavoritesFull()
  } catch (err) {
    console.error('Ошибка загрузки избранного:', err)
  } finally {
    favoritesLoading.value = false
  }
}

const cancelOrder = async (orderId, orderNumber) => {
  if (!confirm(`Отменить заказ #${orderNumber}?`)) return
  try {
    const response = await orderApi.cancelOrder(orderId)
    if (response.data.success) {
      success('Заказ отменён')
      await loadOrders()
      await loadUserBonus()
    } else {
      error(response.data.message || 'Ошибка при отмене')
    }
  } catch (err) {
    error('Не удалось отменить заказ')
  }
}

const repeatOrder = async (orderId) => {
  success('Заказ добавлен в корзину')
}

const switchTab = async (tab) => {
  currentTab.value = tab
  if (tab === 'favorites' && favoritesStore.items.length === 0) {
    await loadFavorites()
  }
  if (tab === 'orders' && orders.value.length === 0) {
    await loadOrders()
  }
}

const favoritesItems = computed(() => favoritesStore.items || [])
const recentOrders = computed(() => orders.value?.slice(0, 3) || [])

const memberSince = computed(() => {
  if (!authStore.user?.created_at) return 'Новый'
  return new Date(authStore.user.created_at).toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long'
  })
})

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

const getStatusText = (status) => {
  const statuses = {
    'new': 'Новый',
    'pending': 'Ожидает оплаты',
    'processing': 'В обработке',
    'shipped': 'Отправлен',
    'delivered': 'Доставлен',
    'cancelled': 'Отменён',
    'paid': 'Оплачен'
  }
  return statuses[status] || status
}

const getStatusClass = (status) => {
  const classes = {
    'new': 'bg-blue-50 text-blue-600',
    'pending': 'bg-orange-50 text-orange-600',
    'processing': 'bg-yellow-50 text-yellow-600',
    'shipped': 'bg-purple-50 text-purple-600',
    'delivered': 'bg-green-50 text-green-600',
    'cancelled': 'bg-red-50 text-red-600',
    'paid': 'bg-teal-50 text-teal-600'
  }
  return classes[status] || 'bg-gray-50 text-gray-600'
}

const canCancelOrder = (status) => {
  return status === 'new' || status === 'pending' || status === 'processing'
}

const viewOrder = (orderId) => {
  router.push(`/orders/${orderId}`)
}

const goToProduct = (book) => {
  if (book.slug) router.push(`/product/${book.slug}`)
  else router.push(`/product/${book.id}`)
}

const handleLogout = async () => {
  await authStore.logout()
  success('Вы вышли из системы')
  router.push('/')
}

onMounted(async () => {
  await loadOrders()
  await loadUserBonus()
  if (currentTab.value === 'favorites') {
    await loadFavorites()
  }
})
</script>