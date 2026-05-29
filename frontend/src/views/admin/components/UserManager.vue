<template>
  <div>
    <!-- Заголовок секции -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
          <i class="fas fa-users text-[#c8a87c] text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-light text-[#2c2c2c]">Управление пользователями</h3>
          <p class="text-xs text-[#8b7355] mt-0.5">Редактирование ролей и просмотр данных</p>
        </div>
      </div>
      <button 
        @click="loadUsers" 
        class="px-4 py-2 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all flex items-center gap-2"
        :disabled="loading"
      >
        <i :class="loading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'" class="text-xs"></i>
        Обновить
      </button>
    </div>

    <!-- Фильтры и поиск -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <!-- Поиск -->
      <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#8b7355] text-sm"></i>
        <input 
          v-model="filters.search"
          type="text"
          placeholder="Поиск по имени или email..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
          @input="debouncedSearch"
        >
      </div>

      <!-- Фильтр по роли -->
      <select v-model="filters.role" class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer" @change="applyFilters">
        <option value="">Все роли</option>
        <option value="user">Пользователи</option>
        <option value="manager">Менеджеры</option>
        <option value="admin">Администраторы</option>
      </select>

      <!-- Сортировка -->
      <select v-model="filters.sort_by" class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer" @change="applyFilters">
        <option value="created_at">По дате регистрации</option>
        <option value="name">По имени</option>
        <option value="email">По email</option>
        <option value="orders_count">По количеству заказов</option>
      </select>
    </div>

    <!-- Таблица пользователей -->
    <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-[#e8e0d8]">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b border-[#e8e0d8] bg-[#faf8f5]">
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">ID</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Аватар</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Имя</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Email</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Роль</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Заказов</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Дата</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && users.length === 0">
            <td colspan="8" class="p-8 text-center">
              <div class="w-6 h-6 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p class="mt-3 text-[#8b7355] text-sm">Загрузка...</p>
            </td>
          </tr>
          <tr v-else-if="users.length === 0">
            <td colspan="8" class="p-8 text-center">
              <i class="fas fa-users text-3xl text-[#e8e0d8] mb-2"></i>
              <p class="text-[#8b7355] text-sm">Пользователи не найдены</p>
            </td>
          </tr>
          <tr v-for="user in users" :key="user.id" class="border-b border-[#e8e0d8] hover:bg-[#faf8f5] transition-colors">
            <td class="p-4 text-[#8b7355] text-sm">{{ user.id }}</td>
            <td class="p-4">
              <div class="w-8 h-8 rounded-full overflow-hidden bg-[#faf8f5] flex items-center justify-center border border-[#e8e0d8]">
                <img 
                  v-if="user.avatar" 
                  :src="user.avatar" 
                  :alt="user.name"
                  class="w-full h-full object-cover"
                >
                <i v-else class="fas fa-user text-[#c8a87c]/30 text-sm"></i>
              </div>
            </td>
            <td class="p-4">
              <div class="font-medium text-[#2c2c2c] text-sm truncate max-w-[150px]" :title="user.name">{{ user.name }}</div>
            </td>
            <td class="p-4">
              <div class="text-[#8b7355] text-sm truncate max-w-[180px]" :title="user.email">{{ user.email }}</div>
            </td>
            <td class="p-4">
              <span 
                class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-medium"
                :class="{
                  'bg-green-50 text-green-600': user.role === 'admin',
                  'bg-blue-50 text-blue-600': user.role === 'manager',
                  'bg-gray-50 text-gray-500': user.role === 'user'
                }"
              >
                <i :class="getRoleIcon(user.role)" class="text-[10px]"></i>
                {{ getRoleName(user.role) }}
              </span>
            </td>
            <td class="p-4 text-[#8b7355] text-sm">{{ user.orders_count || 0 }}</td>
            <td class="p-4 text-[#8b7355] text-sm">{{ formatDate(user.created_at) }}</td>
            <td class="p-4">
              <div class="flex gap-2">
                <button 
                  @click="viewUser(user)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] hover:text-[#c8a87c] transition-all"
                  title="Просмотр"
                >
                  <i class="fas fa-eye text-xs"></i>
                </button>
                
                <button 
                  v-if="authStore.user?.role === 'admin' && user.id !== authStore.user?.id"
                  @click="openRoleModal(user)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] hover:text-[#c8a87c] transition-all"
                  title="Изменить роль"
                >
                  <i class="fas fa-user-tag text-xs"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Пагинация -->
    <div v-if="pagination && pagination.last_page > 1" class="flex justify-between items-center mt-6">
      <div class="text-xs text-[#8b7355]">
        {{ pagination.from || 0 }} - {{ pagination.to || 0 }} из {{ pagination.total }}
      </div>
      <div class="flex gap-2">
        <button 
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page === 1"
          class="w-8 h-8 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
        >
          <i class="fas fa-chevron-left text-xs"></i>
        </button>
        <span class="px-3 py-1.5 rounded-xl bg-[#c8a87c] text-white text-sm">
          {{ pagination.current_page }}
        </span>
        <button 
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page === pagination.last_page"
          class="w-8 h-8 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
        >
          <i class="fas fa-chevron-right text-xs"></i>
        </button>
      </div>
    </div>

    <!-- Модальное окно просмотра пользователя -->
    <div v-if="showViewModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeViewModal">
      <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl transform transition-all animate-modal-slide">
        <div class="flex justify-between items-center p-6 border-b border-[#e8e0d8]">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
              <i class="fas fa-user-circle text-[#c8a87c] text-xl"></i>
            </div>
            <h3 class="text-xl font-light text-[#2c2c2c]">Информация о пользователе</h3>
          </div>
          <button @click="closeViewModal" class="text-[#8b7355] hover:text-[#2c2c2c] transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <div class="p-6">
          <div class="flex items-start gap-6 mb-6 pb-6 border-b border-[#e8e0d8]">
            <div class="w-20 h-20 rounded-full overflow-hidden bg-[#faf8f5] flex items-center justify-center border border-[#e8e0d8]">
              <img 
                v-if="selectedUser?.avatar" 
                :src="selectedUser.avatar" 
                :alt="selectedUser.name"
                class="w-full h-full object-cover"
              >
              <i v-else class="fas fa-user text-[#c8a87c]/30 text-3xl"></i>
            </div>
            
            <div class="flex-1">
              <h4 class="text-2xl font-light text-[#2c2c2c]">{{ selectedUser?.name }}</h4>
              <p class="text-[#8b7355] text-sm mt-1">{{ selectedUser?.email }}</p>
              <div class="flex gap-2 mt-3">
                <span 
                  class="px-2 py-0.5 rounded-full text-[10px] font-medium"
                  :class="{
                    'bg-green-50 text-green-600': selectedUser?.role === 'admin',
                    'bg-blue-50 text-blue-600': selectedUser?.role === 'manager',
                    'bg-gray-50 text-gray-500': selectedUser?.role === 'user'
                  }"
                >
                  <i :class="getRoleIcon(selectedUser?.role)" class="text-[10px] mr-1"></i>
                  {{ getRoleName(selectedUser?.role) }}
                </span>
                <span v-if="selectedUser?.email_verified_at" class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-green-50 text-green-600">
                  <i class="fas fa-check-circle mr-1 text-[9px]"></i>
                  Верифицирован
                </span>
              </div>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div class="p-3 rounded-xl bg-[#faf8f5]">
              <p class="text-[10px] text-[#8b7355] uppercase tracking-wider mb-1">Телефон</p>
              <p class="text-sm text-[#2c2c2c]">{{ selectedUser?.phone || '—' }}</p>
            </div>
            <div class="p-3 rounded-xl bg-[#faf8f5]">
              <p class="text-[10px] text-[#8b7355] uppercase tracking-wider mb-1">Город</p>
              <p class="text-sm text-[#2c2c2c]">{{ selectedUser?.city || '—' }}</p>
            </div>
            <div class="p-3 rounded-xl bg-[#faf8f5]">
              <p class="text-[10px] text-[#8b7355] uppercase tracking-wider mb-1">Адрес</p>
              <p class="text-sm text-[#2c2c2c]">{{ selectedUser?.address_line || '—' }}</p>
            </div>
            <div class="p-3 rounded-xl bg-[#faf8f5]">
              <p class="text-[10px] text-[#8b7355] uppercase tracking-wider mb-1">Заказов</p>
              <p class="text-sm text-[#2c2c2c]">{{ selectedUser?.orders_count || 0 }}</p>
            </div>
            <div class="p-3 rounded-xl bg-[#faf8f5]">
              <p class="text-[10px] text-[#8b7355] uppercase tracking-wider mb-1">Отзывов</p>
              <p class="text-sm text-[#2c2c2c]">{{ selectedUser?.reviews_count || 0 }}</p>
            </div>
            <div class="p-3 rounded-xl bg-[#faf8f5]">
              <p class="text-[10px] text-[#8b7355] uppercase tracking-wider mb-1">В избранном</p>
              <p class="text-sm text-[#2c2c2c]">{{ selectedUser?.favorites_count || 0 }}</p>
            </div>
            <div class="p-3 rounded-xl bg-[#faf8f5] col-span-2">
              <p class="text-[10px] text-[#8b7355] uppercase tracking-wider mb-1">Дата регистрации</p>
              <p class="text-sm text-[#2c2c2c]">{{ formatDate(selectedUser?.created_at) }}</p>
            </div>
          </div>
          
          <div class="flex gap-3 mt-6 pt-4 border-t border-[#e8e0d8]">
            <button 
              @click="closeViewModal"
              class="flex-1 px-4 py-2.5 rounded-xl border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] transition-all text-sm"
            >
              Закрыть
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Модальное окно изменения роли -->
    <div v-if="showRoleModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeRoleModal">
      <div class="bg-white rounded-2xl w-full max-w-md shadow-xl transform transition-all animate-modal-slide">
        <div class="flex justify-between items-center p-6 border-b border-[#e8e0d8]">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
              <i class="fas fa-user-tag text-[#c8a87c] text-lg"></i>
            </div>
            <h3 class="text-xl font-light text-[#2c2c2c]">Изменение роли</h3>
          </div>
          <button @click="closeRoleModal" class="text-[#8b7355] hover:text-[#2c2c2c] transition-colors">
            <i class="fas fa-times text-xl"></i>
          </button>
        </div>
        
        <div class="p-6">
          <div class="mb-5">
            <p class="text-sm text-[#8b7355] mb-1">Пользователь</p>
            <p class="text-lg font-light text-[#2c2c2c]">{{ selectedUser?.name }}</p>
          </div>
          
          <div class="mb-5">
            <p class="text-sm text-[#8b7355] mb-2">Текущая роль</p>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-gray-50 text-gray-500">
              <i :class="getRoleIcon(selectedUser?.role)" class="text-xs"></i>
              {{ getRoleName(selectedUser?.role) }}
            </span>
          </div>
          
          <div class="mb-6">
            <label class="block text-sm text-[#2c2c2c] mb-2 font-medium">Новая роль</label>
            <select v-model="newRole" class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer">
              <option value="user">Пользователь</option>
              <option value="manager">Менеджер</option>
              <option value="admin">Администратор</option>
            </select>
          </div>
          
          <div class="flex gap-3">
            <button 
              @click="updateRole"
              :disabled="loadingRole"
              class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a87c] text-white hover:bg-[#b89a6e] transition-all disabled:opacity-50 text-sm font-medium"
            >
              <i v-if="loadingRole" class="fas fa-spinner fa-spin mr-2"></i>
              Сохранить
            </button>
            <button 
              @click="closeRoleModal"
              class="flex-1 px-4 py-2.5 rounded-xl border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] transition-all text-sm"
            >
              Отмена
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast уведомления -->
    <transition 
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div v-if="toast.show" class="fixed bottom-6 right-6 z-50">
        <div class="px-4 py-2.5 rounded-xl shadow-lg text-white flex items-center gap-2 text-sm" :class="toastClass">
          <i :class="toastIcon"></i>
          {{ toast.message }}
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { userApi } from '@/api/users'

const authStore = useAuthStore()

const users = ref([])
const loading = ref(false)
const loadingRole = ref(false)
const searchTimeout = ref(null)

const filters = reactive({
  search: '',
  role: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  page: 1,
  per_page: 15
})

const pagination = ref(null)

const showViewModal = ref(false)
const showRoleModal = ref(false)
const selectedUser = ref(null)
const newRole = ref('user')

const toast = ref({
  show: false,
  message: '',
  type: 'success'
})

const toastClass = computed(() => ({
  'bg-[#c8a87c]': toast.value.type === 'success',
  'bg-red-500': toast.value.type === 'error',
  'bg-yellow-500': toast.value.type === 'warning',
  'bg-blue-500': toast.value.type === 'info'
}))

const toastIcon = computed(() => {
  const icons = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    warning: 'fas fa-exclamation-triangle',
    info: 'fas fa-info-circle'
  }
  return icons[toast.value.type]
})

const getRoleName = (role) => {
  const roles = {
    user: 'Пользователь',
    manager: 'Менеджер',
    admin: 'Администратор'
  }
  return roles[role] || role
}

const getRoleIcon = (role) => {
  const icons = {
    user: 'fas fa-user',
    manager: 'fas fa-user-tie',
    admin: 'fas fa-crown'
  }
  return icons[role] || 'fas fa-user'
}

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

const loadUsers = async () => {
  loading.value = true
  try {
    const params = {
      page: filters.page,
      per_page: filters.per_page,
      sort_by: filters.sort_by,
      sort_order: filters.sort_order
    }
    if (filters.search) params.search = filters.search
    if (filters.role) params.role = filters.role
    
    const response = await userApi.getUsers(params)
    if (response.data.success) {
      users.value = response.data.data.data
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total,
        from: response.data.data.from,
        to: response.data.data.to
      }
    }
  } catch (err) {
    showToast('Ошибка при загрузке пользователей', 'error')
  } finally {
    loading.value = false
  }
}

const debouncedSearch = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    filters.page = 1
    loadUsers()
  }, 500)
}

const applyFilters = () => {
  filters.page = 1
  loadUsers()
}

const changePage = (page) => {
  if (page < 1 || page > pagination.value?.last_page) return
  filters.page = page
  loadUsers()
}

const viewUser = async (user) => {
  selectedUser.value = user
  showViewModal.value = true
  try {
    const response = await userApi.getUser(user.id)
    if (response.data.success) {
      selectedUser.value = response.data.user
    }
  } catch (err) {
    console.error('Error loading user details:', err)
  }
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedUser.value = null
}

const openRoleModal = (user) => {
  selectedUser.value = user
  newRole.value = user.role
  showRoleModal.value = true
}

const closeRoleModal = () => {
  showRoleModal.value = false
  selectedUser.value = null
  newRole.value = 'user'
}

const updateRole = async () => {
  if (!selectedUser.value) return
  loadingRole.value = true
  try {
    const response = await userApi.assignRole(selectedUser.value.id, { role: newRole.value })
    if (response.data.success) {
      showToast(`Роль изменена на "${getRoleName(newRole.value)}"`)
      await loadUsers()
      closeRoleModal()
    }
  } catch (err) {
    showToast(err.response?.data?.message || 'Ошибка при изменении роли', 'error')
  } finally {
    loadingRole.value = false
  }
}

onMounted(() => {
  loadUsers()
})
</script>

<style scoped>
@keyframes modalSlide {
  from {
    opacity: 0;
    transform: scale(0.95) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.animate-modal-slide {
  animation: modalSlide 0.2s ease-out;
}
</style>