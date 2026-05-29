<!-- components/ui/YandexLoginButton.vue -->
<template>
  <div>
    <button
      @click="handleYandexLogin"
      :disabled="loading"
      type="button"
      class="w-full bg-white hover:bg-gray-100 text-gray-900 font-semibold py-3 px-4 rounded-lg transition-all flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed border border-gray-300"
    >
      <img v-if="!loading" src="/yandex.svg" alt="Yandex" class="w-10 h-10" />
      <i v-else class="fas fa-spinner fa-spin"></i>
      <span>{{ loading ? 'Авторизация...' : 'Войти через Яндекс' }}</span>
    </button>
  </div>
</template>

  
  <script setup>
  import { ref } from 'vue'
  import { useToast } from '@/composables/useToast'
  
  const emit = defineEmits(['success', 'error'])
  const loading = ref(false)
  const { success, error: showError } = useToast()
  
  const handleYandexLogin = async () => {
    loading.value = true
    
    try {
      // Просто редирект на бэкенд
      window.location.href = 'http://127.0.0.1:8000/api/auth/yandex'
    } catch (err) {
      console.error('Yandex auth error:', err)
      showError('Ошибка при авторизации через Яндекс')
      emit('error', err)
      loading.value = false
    }
  }
  </script>