<template>
  <header class="bg-white/95 backdrop-blur-md sticky top-0 z-50 border-b border-[#d4c5b0]/40 shadow-sm">
    <div class="container mx-auto max-w-7xl px-4 md:px-8">
      <!-- Основная строка хедера -->
      <div class="flex items-center justify-between py-4 md:py-5">
        <!-- Бургер-меню кнопка (только на мобильных) -->
        <button 
          @click="toggleMobileMenu" 
          class="md:hidden text-2xl text-[#8b7355] hover:text-[#c8a87c] transition-all focus:outline-none relative z-[60]"
        >
          <i :class="mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'"></i>
        </button>
        
        <!-- Логотип и подзаголовок -->
        <div class="flex-1 md:flex-none text-center md:text-left">
          <router-link to="/" class="font-['Montserrat'] text-2xl sm:text-3xl md:text-4xl font-light tracking-wide block">
            <span class="text-[#2c2c2c]">Леди</span>
            <span class="text-[#c8a87c] font-semibold"> Bag</span>
          </router-link>
          <div class="hidden md:block font-['Montserrat'] font-light italic text-[#8b7355] max-w-xs leading-relaxed text-sm tracking-wide">
            Стиль, который всегда с вами
          </div>
        </div>
        
        <!-- Иконки пользователя и корзины -->
        <div class="flex gap-4 sm:gap-5 items-center relative z-[60]">
          <!-- Авторизованный пользователь -->
          <div v-if="authStore.isAuthenticated" class="relative group">
            <button 
              @click="toggleDropdown" 
              class="flex items-center justify-center text-xl sm:text-2xl text-[#8b7355] hover:text-[#c8a87c] transition-all hover:-translate-y-1 focus:outline-none"
            >
              <!-- Аватар или иконка -->
              <img 
                v-if="getAvatarUrl && !avatarLoadError"
                :src="getAvatarUrl"
                alt="Avatar"
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover border-2 border-[#c8a87c]/30"
                @error="handleAvatarError"
              >
              <i v-else class="fas fa-user-circle"></i>
            </button>
            
            <!-- Выпадающее меню -->
            <div 
              v-show="showDropdown" 
              class="absolute right-0 mt-3 w-72 bg-white rounded-lg shadow-xl border border-[#d4c5b0]/40 overflow-hidden z-50"
              @click.stop
            >
              <!-- Шапка выпадающего меню с аватаром -->
              <div class="px-4 py-3 border-b border-[#d4c5b0]/30 bg-gradient-to-r from-[#faf6f0] to-white">
                <div class="flex items-center gap-3">
                  <!-- Аватар в выпадающем меню -->
                  <div class="w-12 h-12 rounded-full overflow-hidden bg-[#faf6f0] border-2 border-[#c8a87c]/30 flex items-center justify-center">
                    <img 
                      v-if="getAvatarUrl && !avatarLoadError"
                      :src="getAvatarUrl"
                      alt="Avatar"
                      class="w-full h-full object-cover"
                      @error="handleAvatarError"
                    >
                    <i v-else class="fas fa-user text-2xl text-[#c8a87c]"></i>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-[#2c2c2c]">{{ authStore.user?.name }}</p>
                    <p class="text-xs text-[#8b7355] truncate">{{ authStore.user?.email }}</p>
                    <p class="text-xs text-[#c8a87c] mt-0.5 capitalize">
                      {{ getUserRoleText() }}
                    </p>
                  </div>
                </div>
              </div>
              
              <div class="py-2">
                <router-link 
                  to="/account" 
                  class="flex items-center px-4 py-2 text-sm text-[#8b7355] hover:bg-[#faf6f0] hover:text-[#c8a87c] transition-colors"
                  @click="closeAll"
                >
                  <i class="fas fa-user w-5"></i>
                  <span class="ml-2">Профиль</span>
                </router-link>
                <router-link 
                  to="/account?tab=orders" 
                  class="flex items-center px-4 py-2 text-sm text-[#8b7355] hover:bg-[#faf6f0] hover:text-[#c8a87c] transition-colors"
                  @click="closeAll"
                >
                  <i class="fas fa-shopping-bag w-5"></i>
                  <span class="ml-2">Мои заказы</span>
                </router-link>
                <router-link 
                  to="/favorites" 
                  class="flex items-center px-4 py-2 text-sm text-[#8b7355] hover:bg-[#faf6f0] hover:text-[#c8a87c] transition-colors"
                  @click="closeAll"
                >
                  <i class="far fa-heart w-5"></i>
                  <span class="ml-2">Избранное</span>
                </router-link>
                <!-- Админ панель доступна и админу, и менеджеру -->
                <router-link 
                  v-if="hasAdminAccess"
                  to="/admin" 
                  class="flex items-center px-4 py-2 text-sm text-[#8b7355] hover:bg-[#faf6f0] hover:text-[#c8a87c] transition-colors"
                  @click="closeAll"
                >
                  <i class="fas fa-cog w-5"></i>
                  <span class="ml-2">Админ-панель</span>
                </router-link>
                <hr class="my-1 border-[#d4c5b0]/30">
                <button 
                  @click="handleLogout" 
                  class="flex items-center w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors"
                >
                  <i class="fas fa-sign-out-alt w-5"></i>
                  <span class="ml-2">Выйти</span>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Корзина -->
          <router-link to="/cart" class="text-xl sm:text-2xl text-[#8b7355] hover:text-[#c8a87c] transition-all hover:-translate-y-1 relative">
            <i class="fas fa-shopping-bag"></i>
            <span v-if="cartCount > 0" class="absolute -top-2 -right-2 bg-[#c8a87c] text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
              {{ cartCount }}
            </span>
          </router-link>
          
          <!-- Иконка входа для неавторизованных -->
          <router-link v-if="!authStore.isAuthenticated" to="/login" class="text-xl sm:text-2xl text-[#8b7355] hover:text-[#c8a87c] transition-all hover:-translate-y-1">
            <i class="fas fa-sign-in-alt"></i>
          </router-link>
        </div>
      </div>

      <!-- Десктопная навигация -->
      <nav class="hidden md:block mt-4 pb-3">
        <ul class="flex flex-wrap justify-center gap-1">
          <li v-for="item in navItems" :key="item.path">
            <router-link 
              :to="item.path" 
              class="nav-item inline-block px-6 py-2.5 text-[#8b7355] font-medium text-sm uppercase tracking-wide rounded-full transition-all hover:text-[#c8a87c] hover:bg-[#faf6f0] hover:-translate-y-0.5"
              active-class="!text-[#c8a87c] font-semibold bg-[#faf6f0]"
            >
              {{ item.name }}
            </router-link>
          </li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- Мобильное меню (вынесено за пределы header для корректного позиционирования) -->
  <Teleport to="body">
    <Transition name="mobile-menu">
      <div v-if="mobileMenuOpen" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[100] md:hidden" @click="closeMobileMenu">
        <nav class="mobile-nav bg-white w-80 h-full overflow-y-auto shadow-2xl" @click.stop>
          <div class="pt-24 pb-6 px-6">
            <!-- Информация о пользователе в мобильном меню с аватаром -->
            <div v-if="authStore.isAuthenticated" class="mb-6 p-4 bg-[#faf6f0] rounded-xl">
              <div class="flex items-center gap-3">
                <!-- Аватар в мобильном меню -->
                <div class="w-12 h-12 rounded-full overflow-hidden bg-[#faf6f0] border-2 border-[#c8a87c]/30 flex items-center justify-center">
                  <img 
                    v-if="getAvatarUrl && !avatarLoadError"
                    :src="getAvatarUrl"
                    alt="Avatar"
                    class="w-full h-full object-cover"
                    @error="handleAvatarError"
                  >
                  <i v-else class="fas fa-user text-2xl text-[#c8a87c]"></i>
                </div>
                <div class="flex-1">
                  <p class="font-medium text-[#2c2c2c]">{{ authStore.user?.name }}</p>
                  <p class="text-xs text-[#8b7355] truncate">{{ authStore.user?.email }}</p>
                  <p class="text-xs text-[#c8a87c] mt-0.5 capitalize">
                    {{ getUserRoleText() }}
                  </p>
                </div>
              </div>
            </div>
            
            <!-- Подзаголовок в мобильном меню -->
            <div class="mb-6 pb-4 border-b border-[#d4c5b0]/40">
              <p class="font-['Montserrat'] font-light italic text-[#8b7355] text-center text-base tracking-wide">
                Стиль, который всегда с вами
              </p>
            </div>
            
            <!-- Навигационные ссылки -->
            <ul class="space-y-2">
              <li v-for="item in navItems" :key="item.path">
                <router-link 
                  :to="item.path" 
                  class="flex items-center px-4 py-3 text-[#8b7355] font-medium rounded-xl transition-all hover:text-[#c8a87c] hover:bg-[#faf6f0]"
                  active-class="!text-[#c8a87c] bg-[#faf6f0]"
                  @click="closeMobileMenu"
                >
                  <i :class="getIconForPath(item.path)" class="w-6 text-[#c8a87c]"></i>
                  <span class="ml-3">{{ item.name }}</span>
                </router-link>
              </li>
              
              <!-- Админ-панель в мобильном меню (доступна админу и менеджеру) -->
              <li v-if="hasAdminAccess">
                <router-link 
                  to="/admin" 
                  class="flex items-center px-4 py-3 text-[#c8a87c] font-medium rounded-xl transition-all hover:text-[#c8a87c] hover:bg-[#faf6f0] border border-[#d4c5b0]/40"
                  active-class="!text-[#c8a87c] bg-[#faf6f0]"
                  @click="closeMobileMenu"
                >
                  <i class="fas fa-cog w-6"></i>
                  <span class="ml-3">Админ-панель</span>
                </router-link>
              </li>
            </ul>
            
            <!-- Кнопка выхода в мобильном меню для авторизованных -->
            <div v-if="authStore.isAuthenticated" class="mt-6 pt-4 border-t border-[#d4c5b0]/40">
              <button 
                @click="handleLogout" 
                class="flex items-center w-full px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl transition-colors"
              >
                <i class="fas fa-sign-out-alt w-6"></i>
                <span class="ml-3">Выйти</span>
              </button>
            </div>
            
            <!-- Кнопка входа для неавторизованных -->
            <div v-else class="mt-6 pt-4 border-t border-[#d4c5b0]/40">
              <router-link 
                to="/login" 
                class="flex items-center w-full px-4 py-3 text-[#c8a87c] hover:bg-[#faf6f0] rounded-xl transition-colors"
                @click="closeMobileMenu"
              >
                <i class="fas fa-sign-in-alt w-6"></i>
                <span class="ml-3">Войти</span>
              </router-link>
            </div>
          </div>
        </nav>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useRouter, useRoute } from 'vue-router'

const authStore = useAuthStore()
const cartStore = useCartStore()
const router = useRouter()
const route = useRoute()
const showDropdown = ref(false)
const mobileMenuOpen = ref(false)
const avatarLoadError = ref(false)

// URL аватара для отображения
const getAvatarUrl = computed(() => {
  if (avatarLoadError.value) return null
  return authStore.userAvatar || null
})

// Обработка ошибки загрузки аватара
const handleAvatarError = () => {
  avatarLoadError.value = true
}

// Счетчик на основе itemsCount (computed автоматически обновляется)
const cartCount = computed(() => cartStore.itemsCount)

// Проверка доступа к админ-панели (admin или manager)
const hasAdminAccess = computed(() => {
  return authStore.isAuthenticated && 
         (authStore.user?.role === 'admin' || authStore.user?.role === 'manager')
})

// Текст роли пользователя
const getUserRoleText = () => {
  const role = authStore.user?.role
  if (role === 'admin') return 'Администратор'
  if (role === 'manager') return 'Менеджер'
  return 'Покупатель'
}

// Дополнительный watch для принудительного обновления
watch(() => cartStore.lastUpdated, () => {
  // Принудительный пересчёт через nextTick
  cartCount.value
}, { immediate: true })

// Обновленные пункты меню под магазин сумок и аксессуаров
const navItems = [
  { path: '/', name: 'Главная' },
  { path: '/catalog', name: 'Каталог' },
  { path: '/collections', name: 'Коллекции' },
  { path: '/authors', name: 'Бренды' },
  { path: '/about', name: 'О нас' }
]

// Иконки для мобильного меню
const getIconForPath = (path) => {
  const icons = {
    '/': 'fas fa-home',
    '/catalog': 'fas fa-shopping-bag',
    '/collections': 'fas fa-layer-group',
    '/authors': 'fas fa-tag',
    '/about': 'fas fa-info-circle'
  }
  return icons[path] || 'fas fa-circle'
}

// Переключение мобильного меню
const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
  if (mobileMenuOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

// Закрытие мобильного меню
const closeMobileMenu = () => {
  mobileMenuOpen.value = false
  document.body.style.overflow = ''
}

// Переключение выпадающего меню
const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
}

// Закрыть все меню
const closeAll = () => {
  showDropdown.value = false
  closeMobileMenu()
}

// Закрыть дропдаун при клике вне
const closeDropdown = (event) => {
  if (!event.target.closest('.relative')) {
    showDropdown.value = false
  }
}

// Выход из системы
const handleLogout = async () => {
  await authStore.logout()
  closeAll()
  router.push('/')
}

// Закрывать мобильное меню при смене маршрута
watch(() => route.path, () => {
  closeMobileMenu()
})

// Проверка авторизации при монтировании
onMounted(async () => {
  await authStore.checkAuth()
  // Если пользователь авторизован, загружаем корзину
  if (authStore.isAuthenticated) {
    await cartStore.loadCart()
  }
  document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  document.removeEventListener('click', closeDropdown)
  document.body.style.overflow = ''
})

</script>

<style scoped>
.nav-item {
  position: relative;
  transition: all 0.2s ease;
}

.nav-item::before {
  content: '';
  position: absolute;
  bottom: 4px;
  left: 50%;
  transform: translateX(-50%);
  width: 0;
  height: 2px;
  background: linear-gradient(90deg, #c8a87c, #8b7355);
  transition: width 0.3s ease;
  border-radius: 2px;
}

.nav-item:hover::before,
.router-link-active::before {
  width: 60%;
}

/* Анимация для выпадающего меню */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.2s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Анимация для мобильного меню */
.mobile-menu-enter-active,
.mobile-menu-leave-active {
  transition: opacity 0.3s ease;
}

.mobile-menu-enter-active .mobile-nav,
.mobile-menu-leave-active .mobile-nav {
  transition: transform 0.3s ease;
}

.mobile-menu-enter-from,
.mobile-menu-leave-to {
  opacity: 0;
}

.mobile-menu-enter-from .mobile-nav,
.mobile-menu-leave-to .mobile-nav {
  transform: translateX(-100%);
}

.mobile-nav {
  transform: translateX(0);
}

/* Стили для мобильного скролла */
.mobile-nav {
  -webkit-overflow-scrolling: touch;
}
</style>