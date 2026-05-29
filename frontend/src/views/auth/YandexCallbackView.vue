<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
      <div class="text-center">
        <i class="fas fa-spinner fa-spin text-4xl text-[#7f8330] mb-4"></i>
        <p class="text-[#6c6456]">Обработка авторизации...</p>
      </div>
    </div>
  </template>
  
  <script setup>
  import { onMounted } from 'vue'
  import { useRoute } from 'vue-router'
  import { authApi } from '@/api/auth'
  
  const route = useRoute()
  
  onMounted(async () => {
    const code = route.query.code
    const state = route.query.state
    
    if (!code) {
      sendError('Код авторизации не получен')
      return
    }
    
    try {
      const response = await authApi.handleYandexCallback(code, state)
      
      if (response.data.success) {
        // Отправляем успех в родительское окно
        sendSuccess(response.data.user)
      } else {
        sendError(response.data.message || 'Ошибка авторизации')
      }
    } catch (error) {
      sendError(error.response?.data?.message || 'Ошибка при авторизации')
    }
  })
  
  const sendSuccess = (user) => {
    if (window.opener) {
      window.opener.postMessage({
        type: 'yandex_auth_success',
        user
      }, window.location.origin)
    }
    window.close()
  }
  
  const sendError = (message) => {
    if (window.opener) {
      window.opener.postMessage({
        type: 'yandex_auth_error',
        message
      }, window.location.origin)
    }
    window.close()
  }
  </script>