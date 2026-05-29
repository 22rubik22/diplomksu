<template>
  <div class="min-h-screen bg-[#faf8f5]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <!-- Шапка страницы -->
      <div class="flex items-center justify-between mb-8 pb-4 border-b border-black/5">
        <div>
          <p class="text-[11px] tracking-[0.3em] uppercase text-[#c8a87c] font-light">Admin panel</p>
          <h1 class="text-2xl sm:text-3xl font-light text-[#2c2c2c] mt-1">Администрирование</h1>
        </div>
        <div class="hidden sm:flex items-center gap-2 text-xs text-[#8b7355]">
          <span class="w-2 h-2 rounded-full bg-[#c8a87c]"></span>
          <span>{{ authStore.user?.role === 'admin' ? 'Полный доступ' : 'Управление заказами' }}</span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-[260px_1fr] gap-8">
        <!-- Боковое меню админа -->
        <div>
          <div class="bg-white rounded-2xl p-4 shadow-sm border border-[#e8e0d8] sticky top-24">
            <div class="mb-4 pb-3 border-b border-[#e8e0d8]">
              <p class="text-[10px] tracking-[0.3em] uppercase text-[#c8a87c] font-light">Navigation</p>
            </div>
            <ul class="space-y-1">
              <li v-for="item in visibleAdminMenu" :key="item.id">
                <button
                  @click="activeTab = item.id"
                  class="w-full text-left px-4 py-2.5 rounded-xl transition-all text-sm flex items-center gap-3"
                  :class="activeTab === item.id 
                    ? 'bg-[#c8a87c]/10 text-[#c8a87c] font-medium' 
                    : 'text-[#8b7355] hover:bg-[#faf8f5]'"
                >
                  <i :class="item.icon" class="w-5 text-center"></i>
                  {{ item.name }}
                  <span v-if="getBadgeCount(item.id)" class="ml-auto text-[10px] bg-[#c8a87c]/20 text-[#c8a87c] px-1.5 py-0.5 rounded-full">
                    {{ getBadgeCount(item.id) }}
                  </span>
                </button>
              </li>
            </ul>
          </div>
        </div>

        <!-- Контент вкладок -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#e8e0d8] overflow-hidden">
          <div class="p-6">
            <!-- Жанры -->
            <div v-if="activeTab === 'genres' && authStore.user?.role === 'admin'">
              <GenreManager />
            </div>

            <!-- Авторы -->
            <div v-if="activeTab === 'authors' && authStore.user?.role === 'admin'">
              <AuthorManager />
            </div>

            <!-- Книги -->
            <div v-if="activeTab === 'books' && authStore.user?.role === 'admin'">
              <BookManager />
            </div>

            <!-- Заказы -->
            <div v-if="activeTab === 'orders'">
              <OrderManager />
            </div>

            <!-- Отзывы -->
            <div v-if="activeTab === 'reviews'">
              <ReviewManager />
            </div>



            <!-- Пользователи -->
            <div v-if="activeTab === 'users' && authStore.user?.role === 'admin'">
              <UserManager />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import GenreManager from './components/GenreManager.vue'
import AuthorManager from './components/AuthorManager.vue'
import BookManager from './components/BookManager.vue'
import OrderManager from './components/OrderManager.vue'
import ReviewManager from './components/ReviewManager.vue'
import UserManager from './components/UserManager.vue'

const authStore = useAuthStore()
const activeTab = ref('books')

const isAdmin = computed(() => authStore.user?.role === 'admin')

const adminMenu = [
  { id: 'users', name: 'Пользователи', icon: 'fas fa-users', badge: 'new' },
  { id: 'books', name: 'Товары', icon: 'fas fa-shopping-bag', badge: null },
  { id: 'authors', name: 'Бренды', icon: 'fas fa-tag', badge: null },
  { id: 'genres', name: 'Категории', icon: 'fas fa-folder', badge: null },
  { id: 'orders', name: 'Заказы', icon: 'fas fa-shopping-cart', badge: 'pending' },
  { id: 'reviews', name: 'Отзывы', icon: 'fas fa-star', badge: 'pending' }
]

const visibleAdminMenu = computed(() => {
  if (isAdmin.value) {
    return adminMenu
  }
  return adminMenu.filter(item => 
    !['users', 'books', 'authors', 'genres'].includes(item.id)
  )
})

const availableTabs = computed(() => {
  if (isAdmin.value) {
    return ['users', 'books', 'authors', 'genres', 'orders', 'reviews']
  }
  return ['orders', 'reviews']
})

// Бейджи для пунктов меню
const getBadgeCount = (tabId) => {
  // TODO: Реализовать получение количества новых заказов/отзывов
  if (tabId === 'orders') return 3
  if (tabId === 'reviews') return 2
  if (tabId === 'users') return 1
  return null
}

watch(() => authStore.user?.role, () => {
  if (!isAdmin.value && !availableTabs.value.includes(activeTab.value)) {
    activeTab.value = 'orders'
  }
}, { immediate: true })
</script>