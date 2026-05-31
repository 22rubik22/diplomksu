<template>
  <div class="max-w-md mx-auto">
    <h1 class="page-title">Регистрация</h1>
    
    <div class="bg-white rounded-xl p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
      <form @submit.prevent="handleRegister">
        <div class="space-y-5">
          <div>
            <label class="block text-[#5e1104] mb-2">Имя</label>
            <input 
              v-model="form.name"
              type="text" 
              class="form-input"
              :class="{ 'border-red-500': errors.name }"
              required
            >
            <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</p>
          </div>
          
          <div>
            <label class="block text-[#5e1104] mb-2">Email</label>
            <input 
              v-model="form.email"
              type="email" 
              class="form-input"
              :class="{ 'border-red-500': errors.email }"
              placeholder="your@email.com"
              required
            >
            <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email[0] }}</p>
          </div>
          
          <div>
            <label class="block text-[#5e1104] mb-2">Телефон (необязательно)</label>
            <input 
              v-model="form.phone"
              type="tel" 
              class="form-input"
              placeholder="+7 (999) 123-45-67"
            >
            <p v-if="errors.phone" class="text-red-500 text-sm mt-1">{{ errors.phone[0] }}</p>
          </div>
          
          <div>
            <label class="block text-[#5e1104] mb-2">Пароль</label>
            <input 
              v-model="form.password"
              type="password" 
              class="form-input"
              :class="{ 'border-red-500': errors.password }"
              placeholder="минимум 8 символов"
              required
            >
            <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password[0] }}</p>
          </div>
          
          <div>
            <label class="block text-[#5e1104] mb-2">Подтверждение пароля</label>
            <input 
              v-model="form.password_confirmation"
              type="password" 
              class="form-input"
              :class="{ 'border-red-500': errors.password_confirmation }"
              required
            >
            <p v-if="errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ errors.password_confirmation[0] }}</p>
          </div>

          <button 
            type="submit"
            class="btn-primary w-full py-3"
            :disabled="loading"
          >
            <span v-if="loading" class="inline-flex items-center gap-2">
              <i class="fas fa-spinner fa-spin"></i>
              Регистрация...
            </span>
            <span v-else>Зарегистрироваться</span>
          </button>

          <!-- Разделитель -->
          <div class="relative my-4">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-gray-500">Или</span>
            </div>
          </div>

          <!-- Кнопка Яндекс входа -->
          <YandexLoginButton 
            @success="onYandexSuccess"
            @error="onYandexError"
          />

          <div class="text-center mt-4">
            <span class="text-[#6c6456]">Уже есть аккаунт?</span>
            <router-link to="/login" class="text-[#7f8330] hover:underline ml-2">
              Войти
            </router-link>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from '@/composables/useToast'
import YandexLoginButton from '@/components/ui/YandexLoginButton.vue'

const router = useRouter()
const authStore = useAuthStore()
const { success, error: showError } = useToast()

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const errors = ref({})

const handleRegister = async () => {
  errors.value = {}
  loading.value = true
  
  try {
    const result = await authStore.register(form.value)
    
    if (result.success) {
      success('Регистрация прошла успешно!')
      router.push('/')
    } else if (result.errors) {
      errors.value = result.errors
    } else {
      showError(result.message || 'Ошибка при регистрации')
    }
  } catch (error) {
    console.error('Register error:', error)
    showError('Произошла ошибка при регистрации')
  } finally {
    loading.value = false
  }
}

const onYandexSuccess = async (user) => {
  success(`Добро пожаловать, ${user.name}!`)
  router.push('/')
}

const onYandexError = (err) => {
  console.error('Yandex auth error:', err)
}
</script>

<style scoped>
.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
  background-color: #fefaf7;
}

.form-input:focus {
  outline: none;
  border-color: black;
  box-shadow: 0 0 0 3px rgba(127, 131, 48, 0.1);
}

.form-input.border-red-500 {
  border-color: #ef4444;
}

.btn-primary {
  background: black;
  color: white;
  font-weight: 500;
  border-radius: 0.75rem;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(94, 17, 4, 0.2);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  color: black;
  text-align: center;
  margin-bottom: 2rem;
  position: relative;
}

.page-title::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 60px;
  height: 3px;
  background: black;
  border-radius: 2px;
}
</style>