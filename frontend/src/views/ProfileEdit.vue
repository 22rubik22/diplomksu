<template>
  <div>
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
        <i class="fas fa-user-edit text-[#c8a87c] text-lg"></i>
      </div>
      <div>
        <h3 class="text-xl font-light text-[#2c2c2c]">Редактирование профиля</h3>
        <p class="text-xs text-[#8b7355] mt-0.5">Управление личными данными</p>
      </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Левая колонка: аватар -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-[#e8e0d8]">
          <i class="fas fa-image text-[#c8a87c] text-sm"></i>
          <span class="text-sm font-medium text-[#2c2c2c]">Аватар профиля</span>
        </div>
        
        <div class="flex flex-col items-center text-center">
          <!-- Превью аватара -->
          <div class="relative mb-4">
            <div class="w-32 h-32 rounded-full overflow-hidden bg-[#faf8f5] border-2 border-[#e8e0d8] flex items-center justify-center">
              <img 
                v-if="getAvatarUrl"
                :src="getAvatarUrl"
                alt="Avatar"
                class="w-full h-full object-cover"
                @error="handleImageError"
              >
              <i v-else class="fas fa-user text-5xl text-[#c8a87c]/30"></i>
            </div>
            
            <!-- Кнопка редактирования -->
            <label 
              class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-[#c8a87c] text-white flex items-center justify-center cursor-pointer hover:bg-[#b89a6e] transition-all shadow-md"
            >
              <i class="fas fa-camera text-xs"></i>
              <input 
                type="file" 
                accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                class="hidden"
                @change="handleAvatarChange"
              >
            </label>
          </div>
          
          <p class="text-xs text-[#8b7355] mb-3">
            Рекомендуемый размер: 200x200px<br>
            Форматы: JPG, PNG, GIF, WebP (до 2MB)
          </p>
          
          <div class="flex gap-3">
            <button 
              v-if="avatarFile"
              @click="uploadAvatar"
              class="px-4 py-2 bg-[#c8a87c] text-white rounded-xl text-xs font-medium hover:bg-[#b89a6e] transition-all"
              :disabled="uploadingAvatar"
            >
              <i v-if="uploadingAvatar" class="fas fa-spinner fa-spin mr-1"></i>
              Сохранить
            </button>
            <button 
              v-if="authStore.userAvatar && !avatarFile && !isGravatarAvatar && !imageLoadError"
              @click="removeAvatar"
              class="px-4 py-2 border border-red-300 text-red-500 rounded-xl text-xs font-medium hover:bg-red-50 transition-all"
              :disabled="uploadingAvatar"
            >
              <i v-if="uploadingAvatar" class="fas fa-spinner fa-spin mr-1"></i>
              Удалить
            </button>
            <button 
              v-if="avatarFile"
              @click="cancelAvatarChange"
              class="px-4 py-2 border border-[#e8e0d8] text-[#8b7355] rounded-xl text-xs font-medium hover:bg-[#faf8f5] transition-all"
            >
              Отмена
            </button>
          </div>
          
          <!-- Индикатор Gravatar -->
          <div v-if="isGravatarAvatar && !imageLoadError" class="mt-3 text-center">
            <p class="text-[10px] text-[#8b7355]">
              <i class="fas fa-info-circle mr-1"></i>
              Используется Gravatar на основе вашего email
            </p>
          </div>
        </div>
      </div>
      
      <!-- Правая колонка: форма профиля -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-[#e8e0d8]">
          <i class="fas fa-user text-[#c8a87c] text-sm"></i>
          <span class="text-sm font-medium text-[#2c2c2c]">Личная информация</span>
        </div>
        
        <form @submit.prevent="saveProfile" class="space-y-4">
          <div>
            <label class="block text-xs text-[#8b7355] mb-1.5 font-light">
              Имя <span class="text-red-400">*</span>
            </label>
            <input 
              v-model="profileForm.name"
              type="text"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
              :class="{ 'border-red-400': errors.name }"
              required
            >
            <p v-if="errors.name" class="text-red-400 text-xs mt-1">{{ errors.name[0] }}</p>
          </div>
          
          <div>
            <label class="block text-xs text-[#8b7355] mb-1.5 font-light">
              Email
            </label>
            <input 
              :value="authStore.userEmail"
              type="email"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-[#faf8f5] text-[#8b7355] cursor-not-allowed"
              disabled
            >
            <p class="text-[10px] text-[#8b7355] mt-1">Email нельзя изменить</p>
          </div>
          
          <div>
            <label class="block text-xs text-[#8b7355] mb-1.5 font-light">
              Телефон
            </label>
            <input 
              v-model="profileForm.phone"
              type="tel"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
              placeholder="+7 (123) 456-78-90"
            >
          </div>
          
          <div>
            <label class="block text-xs text-[#8b7355] mb-1.5 font-light">
              Город
            </label>
            <input 
              v-model="profileForm.city"
              type="text"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
              placeholder="Москва"
            >
          </div>
          
          <div>
            <label class="block text-xs text-[#8b7355] mb-1.5 font-light">
              Адрес
            </label>
            <textarea 
              v-model="profileForm.address_line"
              rows="2"
              class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all resize-none"
              placeholder="ул. Пушкина, д. 10, кв. 5"
            ></textarea>
          </div>
          
          <div class="flex gap-3 pt-4">
            <button 
              type="submit"
              class="px-6 py-2.5 bg-[#c8a87c] text-white rounded-xl text-sm font-medium hover:bg-[#b89a6e] transition-all disabled:opacity-50"
              :disabled="authStore.loading"
            >
              <i v-if="authStore.loading" class="fas fa-spinner fa-spin mr-2"></i>
              Сохранить изменения
            </button>
            <button 
              type="button"
              @click="resetProfileForm"
              class="px-6 py-2.5 border border-[#e8e0d8] text-[#8b7355] rounded-xl text-sm font-light hover:bg-[#faf8f5] transition-all"
            >
              Отмена
            </button>
          </div>
        </form>
      </div>
      
      <!-- Безопасность -->
      <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8] lg:col-span-2">
        <div class="flex items-center gap-2 mb-5 pb-3 border-b border-[#e8e0d8]">
          <i class="fas fa-lock text-[#c8a87c] text-sm"></i>
          <span class="text-sm font-medium text-[#2c2c2c]">Безопасность</span>
        </div>
        
        <form @submit.prevent="changePassword" class="max-w-md">
          <div class="grid grid-cols-1 gap-4">
            <div>
              <label class="block text-xs text-[#8b7355] mb-1.5 font-light">
                Текущий пароль <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="passwordForm.current_password"
                type="password"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
                :class="{ 'border-red-400': passwordErrors.current_password }"
                required
              >
              <p v-if="passwordErrors.current_password" class="text-red-400 text-xs mt-1">{{ passwordErrors.current_password[0] }}</p>
            </div>
            
            <div>
              <label class="block text-xs text-[#8b7355] mb-1.5 font-light">
                Новый пароль <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="passwordForm.new_password"
                type="password"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
                :class="{ 'border-red-400': passwordErrors.new_password }"
                required
              >
              <p class="text-[10px] text-[#8b7355] mt-1">Минимум 8 символов</p>
              <p v-if="passwordErrors.new_password" class="text-red-400 text-xs mt-1">{{ passwordErrors.new_password[0] }}</p>
            </div>
            
            <div>
              <label class="block text-xs text-[#8b7355] mb-1.5 font-light">
                Подтверждение пароля <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="passwordForm.new_password_confirmation"
                type="password"
                class="w-full px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] focus:ring-1 focus:ring-[#c8a87c] transition-all"
                :class="{ 'border-red-400': passwordErrors.new_password_confirmation }"
                required
              >
            </div>
            
            <div class="pt-4">
              <button 
                type="submit"
                class="px-6 py-2.5 bg-white border border-[#c8a87c] text-[#c8a87c] rounded-xl text-sm font-medium hover:bg-[#c8a87c] hover:text-white transition-all disabled:opacity-50"
                :disabled="authStore.loading"
              >
                <i v-if="authStore.loading" class="fas fa-spinner fa-spin mr-2"></i>
                Сменить пароль
              </button>
            </div>
          </div>
        </form>
        
        <div class="mt-6 pt-4 border-t border-[#e8e0d8]">
          <p class="text-[10px] text-[#8b7355] flex items-center gap-2">
            <i class="fas fa-shield-alt text-[#c8a87c]"></i>
            Используйте надёжный пароль из букв, цифр и символов
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'

const authStore = useAuthStore()
const { success, error } = useToast()

const errors = ref({})
const passwordErrors = ref({})
const avatarFile = ref(null)
const avatarPreview = ref(null)
const uploadingAvatar = ref(false)
const imageLoadError = ref(false)

const profileForm = ref({
  name: '',
  phone: '',
  city: '',
  address_line: ''
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

// URL аватара для отображения
const getAvatarUrl = computed(() => {
  if (avatarPreview.value) return avatarPreview.value
  if (authStore.userAvatar && !imageLoadError.value) return authStore.userAvatar
  return null
})

// Проверка, является ли аватар Gravatar
const isGravatarAvatar = computed(() => {
  return authStore.userAvatar?.includes('gravatar.com') || false
})

const handleImageError = () => {
  imageLoadError.value = true
}

const initForm = () => {
  if (authStore.user) {
    profileForm.value = {
      name: authStore.userName || '',
      phone: authStore.userPhone || '',
      city: authStore.userCity || '',
      address_line: authStore.userAddress || ''
    }
  }
  // Сбрасываем ошибку загрузки при инициализации
  imageLoadError.value = false
}

const resetProfileForm = () => {
  initForm()
  errors.value = {}
}

const saveProfile = async () => {
  errors.value = {}
  
  const result = await authStore.updateProfile(profileForm.value)
  
  if (result.success) {
    success(result.message)
    initForm()
  } else {
    if (result.errors) {
      errors.value = result.errors
    }
    error(result.message)
  }
}

const changePassword = async () => {
  passwordErrors.value = {}
  
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    error('Новый пароль и подтверждение не совпадают')
    return
  }
  
  const result = await authStore.changePassword(passwordForm.value)
  
  if (result.success) {
    success(result.message)
    passwordForm.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    }
  } else {
    if (result.errors) {
      passwordErrors.value = result.errors
    }
    error(result.message)
  }
}

// Обработка выбора файла аватара
const handleAvatarChange = (event) => {
  const file = event.target.files[0]
  if (!file) return
  
  // Проверка размера (2MB)
  if (file.size > 2 * 1024 * 1024) {
    error('Файл слишком большой. Максимальный размер 2MB')
    return
  }
  
  // Проверка типа
  const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    error('Неподдерживаемый формат. Используйте JPG, PNG, GIF или WebP')
    return
  }
  
  avatarFile.value = file
  avatarPreview.value = URL.createObjectURL(file)
  imageLoadError.value = false
}

// Загрузка аватара
const uploadAvatar = async () => {
  if (!avatarFile.value) return
  
  uploadingAvatar.value = true
  
  const result = await authStore.updateAvatar(avatarFile.value)
  
  if (result.success) {
    success(result.message)
    cancelAvatarChange()
    // Принудительно обновляем данные пользователя
    await authStore.fetchUser()
    imageLoadError.value = false
  } else {
    error(result.message)
  }
  
  uploadingAvatar.value = false
}

// Удаление аватара
const removeAvatar = async () => {
  if (!confirm('Вы уверены, что хотите удалить аватар?')) return
  
  uploadingAvatar.value = true
  
  const result = await authStore.deleteAvatar()
  
  if (result.success) {
    success(result.message)
    await authStore.fetchUser()
    imageLoadError.value = false
  } else {
    error(result.message)
  }
  
  uploadingAvatar.value = false
}

// Отмена изменений аватара
const cancelAvatarChange = () => {
  if (avatarPreview.value) {
    URL.revokeObjectURL(avatarPreview.value)
  }
  avatarFile.value = null
  avatarPreview.value = null
  imageLoadError.value = false
}

onMounted(() => {
  initForm()
})
</script>