<!-- components/ui/CardPaymentForm.vue -->
<template>
  <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-xl">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-xl font-bold text-[#5e1104]">Оплата картой</h3>
      <button @click="$emit('cancel')" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times"></i>
      </button>
    </div>
    
    <div class="text-center mb-6">
      <div class="text-2xl font-bold text-[#5e1104]">{{ formatPrice(amount) }} ₽</div>
      <p class="text-sm text-gray-500 mt-1">Сумма к оплате</p>
    </div>

    <!-- Контейнер для виджета ЮKassa -->
    <div id="yookassa-widget" class="mb-4"></div>

    <div class="text-xs text-gray-400 text-center">
      <i class="fas fa-lock mr-1"></i>
      Безопасная оплата через ЮKassa
    </div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted } from 'vue'
import api from '@/api/axios'

const props = defineProps({
  amount: {
    type: Number,
    required: true
  },
  confirmationUrl: {
    type: String,
    required: true
  },
  paymentId: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['success', 'cancel', 'error'])

const formatPrice = (price) => {
  return new Intl.NumberFormat('ru-RU').format(price)
}

let intervalId = null
let checkClosedInterval = null

onMounted(() => {
  // Открываем окно оплаты ЮKassa
  const checkoutWindow = window.open(props.confirmationUrl, '_blank', 'width=600,height=700')
  
  if (!checkoutWindow) {
    emit('error', 'Браузер заблокировал всплывающее окно. Разрешите всплывающие окна для этого сайта.')
    return
  }
  
  // Проверяем статус платежа каждые 2 секунды
  intervalId = setInterval(async () => {
    try {
      const response = await api.post('/payments/check', {
        payment_id: props.paymentId
      })
      
      if (response.data.success && response.data.data.paid) {
        clearInterval(intervalId)
        if (checkClosedInterval) clearInterval(checkClosedInterval)
        if (!checkoutWindow.closed) {
          checkoutWindow.close()
        }
        emit('success', {
          payment_id: props.paymentId,
          status: 'succeeded'
        })
      }
    } catch (err) {
      console.error('Ошибка проверки статуса:', err)
    }
  }, 2000)
  
  // Закрываем интервал, если пользователь закрыл окно
  checkClosedInterval = setInterval(() => {
    if (checkoutWindow.closed) {
      clearInterval(checkClosedInterval)
      clearInterval(intervalId)
      emit('cancel')
    }
  }, 500)
})

onUnmounted(() => {
  if (intervalId) {
    clearInterval(intervalId)
  }
  if (checkClosedInterval) {
    clearInterval(checkClosedInterval)
  }
})
</script>