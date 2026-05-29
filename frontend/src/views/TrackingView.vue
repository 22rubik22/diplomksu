<template>
    <div>
      <h1 class="page-title">Отслеживание заказа</h1>
      
      <div class="bg-white rounded-xl p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
        <!-- Форма поиска заказа -->
        <div class="max-w-md mx-auto mb-12">
          <div class="flex gap-3">
            <input 
              v-model="orderNumber"
              type="text"
              placeholder="Введите номер заказа"
              class="form-input flex-1"
            >
            <button @click="trackOrder" class="btn-primary px-6">
              Отследить
            </button>
          </div>
        </div>
        
        <!-- Статус заказа (показываем, если есть номер) -->
        <div v-if="trackingNumber" class="max-w-2xl mx-auto">
          <h3 class="text-[#5e1104] text-2xl mb-6 text-center">
            Статус заказа #{{ trackingNumber }}
          </h3>
          
          <!-- Трекер статусов -->
          <div class="relative">
            <!-- Линия -->
            <div class="absolute left-8 top-8 bottom-8 w-0.5 bg-[#7f8330]/20"></div>
            
            <!-- Статусы -->
            <div class="space-y-8">
              <div v-for="(status, index) in orderStatuses" :key="index" class="flex gap-4 relative">
                <div 
                  class="w-16 h-16 rounded-full flex items-center justify-center z-10"
                  :class="status.completed ? 'bg-[#7f8330]' : 'bg-[#7f8330]/10'"
                >
                  <i 
                    v-if="status.completed" 
                    class="fas fa-check text-white text-xl"
                  ></i>
                  <span v-else class="text-[#7f8330] font-medium">{{ index + 1 }}</span>
                </div>
                
                <div class="flex-1 pt-3">
                  <h4 class="font-medium text-[#5e1104]">{{ status.title }}</h4>
                  <p class="text-sm text-[#6c6456]">{{ status.date }}</p>
                  <p v-if="status.description" class="text-sm text-[#6c6456] mt-1">
                    {{ status.description }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Информация о доставке -->
          <div class="mt-8 p-6 bg-[#7f8330]/5 rounded-xl">
            <h4 class="text-[#5e1104] font-medium mb-3">Детали доставки</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-[#6c6456]">Служба доставки</p>
                <p class="font-medium">СДЭК</p>
              </div>
              <div>
                <p class="text-[#6c6456]">Трек-номер</p>
                <p class="font-medium">CDEK123456789</p>
              </div>
              <div>
                <p class="text-[#6c6456]">Вес заказа</p>
                <p class="font-medium">1.2 кг</p>
              </div>
              <div>
                <p class="text-[#6c6456]">Количество мест</p>
                <p class="font-medium">1</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Сообщение, если заказ не найден -->
        <div v-else-if="searched" class="text-center py-12">
          <i class="fas fa-search text-5xl text-[#7f8330]/30 mb-4"></i>
          <p class="text-lg text-[#6c6456]">Заказ не найден</p>
          <p class="text-sm text-[#6c6456] mt-2">Проверьте номер заказа или обратитесь в поддержку</p>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  const orderNumber = ref('')
  const trackingNumber = ref('')
  const searched = ref(false)
  
  const orderStatuses = ref([
    {
      title: 'Заказ оформлен',
      date: '20 января 2026, 15:30',
      completed: true,
      description: 'Заказ принят и передан в обработку'
    },
    {
      title: 'Оплата получена',
      date: '20 января 2026, 15:35',
      completed: true
    },
    {
      title: 'Собирается на складе',
      date: '21 января 2026, 10:15',
      completed: true,
      description: 'Товары упаковываются'
    },
    {
      title: 'Передан в доставку',
      date: 'Ожидается 23-24 января',
      completed: false
    }
  ])
  
  const trackOrder = () => {
    searched.value = true
    if (orderNumber.value) {
      trackingNumber.value = orderNumber.value
      // Здесь будет запрос к API
    } else {
      trackingNumber.value = ''
    }
  }
  </script>