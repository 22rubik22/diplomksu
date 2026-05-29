<template>
  <div class="min-h-screen bg-[#faf8f5]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
      <!-- Шапка страницы -->
      <div class="text-center mb-12">
        <p class="text-[11px] tracking-[0.3em] uppercase text-[#c8a87c] font-light">Info</p>
        <h1 class="text-3xl sm:text-4xl font-light text-[#2c2c2c] tracking-tight mt-1">Доставка и оплата</h1>
        <div class="w-12 h-px bg-[#c8a87c]/30 mx-auto mt-4"></div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Доставка -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
          <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#e8e0d8]">
            <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
              <i class="fas fa-truck text-[#c8a87c] text-lg"></i>
            </div>
            <h3 class="text-xl font-light text-[#2c2c2c]">Способы доставки</h3>
          </div>
          
          <div class="space-y-6">
            <div v-for="delivery in deliveryMethods" :key="delivery.id" class="flex gap-4 group">
              <div class="w-12 h-12 rounded-xl bg-[#faf8f5] flex items-center justify-center flex-shrink-0 group-hover:bg-[#c8a87c]/10 transition-all">
                <i :class="delivery.icon" class="text-xl text-[#c8a87c]"></i>
              </div>
              <div>
                <h4 class="text-[#2c2c2c] font-medium mb-1">{{ delivery.name }}</h4>
                <p class="text-[#8b7355] text-sm mb-2 leading-relaxed">{{ delivery.description }}</p>
                <p class="text-[#c8a87c] text-sm font-medium">{{ delivery.price }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Оплата -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
          <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#e8e0d8]">
            <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
              <i class="fas fa-credit-card text-[#c8a87c] text-lg"></i>
            </div>
            <h3 class="text-xl font-light text-[#2c2c2c]">Способы оплаты</h3>
          </div>
          
          <div class="space-y-6">
            <div v-for="payment in paymentMethods" :key="payment.id" class="flex gap-4 group">
              <div class="w-12 h-12 rounded-xl bg-[#faf8f5] flex items-center justify-center flex-shrink-0 group-hover:bg-[#c8a87c]/10 transition-all">
                <i :class="payment.icon" class="text-xl text-[#c8a87c]"></i>
              </div>
              <div>
                <h4 class="text-[#2c2c2c] font-medium mb-1">{{ payment.name }}</h4>
                <p class="text-[#8b7355] text-sm leading-relaxed">{{ payment.description }}</p>
              </div>
            </div>
          </div>
          
          <!-- Иконки платежных систем -->
          <div class="mt-6 pt-4 border-t border-[#e8e0d8]">
            <p class="text-xs text-[#8b7355] mb-3">Принимаем к оплате:</p>
            <div class="flex gap-3">
              <div class="w-10 h-6 bg-[#faf8f5] rounded flex items-center justify-center text-xs text-[#8b7355]">Visa</div>
              <div class="w-10 h-6 bg-[#faf8f5] rounded flex items-center justify-center text-xs text-[#8b7355]">MC</div>
              <div class="w-10 h-6 bg-[#faf8f5] rounded flex items-center justify-center text-xs text-[#8b7355]">МИР</div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Зоны доставки -->
      <div class="mt-8 bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#e8e0d8]">
          <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
            <i class="fas fa-map-marker-alt text-[#c8a87c] text-lg"></i>
          </div>
          <h3 class="text-xl font-light text-[#2c2c2c]">Зоны доставки</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div v-for="zone in deliveryZones" :key="zone.city" class="p-4 rounded-xl border border-[#e8e0d8] hover:border-[#c8a87c]/50 hover:shadow-sm transition-all">
            <h4 class="text-[#2c2c2c] font-medium mb-2">{{ zone.city }}</h4>
            <p class="text-sm text-[#8b7355]">{{ zone.time }}</p>
            <p class="text-[#c8a87c] text-sm font-medium mt-1">{{ zone.price }}</p>
          </div>
        </div>
      </div>
      
      <!-- Возврат -->
      <div class="mt-8 bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8]">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-[#e8e0d8]">
          <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
            <i class="fas fa-undo-alt text-[#c8a87c] text-lg"></i>
          </div>
          <h3 class="text-xl font-light text-[#2c2c2c]">Условия возврата</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <ul class="space-y-3">
            <li v-for="item in returnRulesLeft" :key="item" class="flex items-start gap-3">
              <i class="fas fa-check text-[#c8a87c] text-sm mt-0.5"></i>
              <span class="text-sm text-[#8b7355]">{{ item }}</span>
            </li>
          </ul>
          <ul class="space-y-3">
            <li v-for="item in returnRulesRight" :key="item" class="flex items-start gap-3">
              <i class="fas fa-check text-[#c8a87c] text-sm mt-0.5"></i>
              <span class="text-sm text-[#8b7355]">{{ item }}</span>
            </li>
          </ul>
        </div>
        
        <div class="mt-6 p-4 bg-[#faf8f5] rounded-xl">
          <div class="flex items-start gap-3">
            <i class="fas fa-clock text-[#c8a87c] text-sm mt-0.5"></i>
            <div>
              <p class="text-sm text-[#2c2c2c] font-medium mb-1">Срок возврата</p>
              <p class="text-sm text-[#8b7355]">Возврат товара возможен в течение 14 дней с момента получения</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Дополнительная информация -->
      <div class="mt-8 text-center">
        <p class="text-xs text-[#8b7355]">
          <i class="fas fa-lock mr-1 text-[#c8a87c]"></i>
          Безопасная оплата картами онлайн. Все данные защищены.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
const deliveryMethods = [
  {
    id: 1,
    name: 'Курьерская доставка',
    icon: 'fas fa-truck',
    description: 'Доставка по городу. Курьер приедет в удобное для вас время.',
    price: '300 ₽'
  },
  {
    id: 2,
    name: 'Самовывоз',
    icon: 'fas fa-store',
    description: 'Бесплатное получение из нашего магазина.',
    price: '0 ₽'
  },
  {
    id: 3,
    name: 'Почта России',
    icon: 'fas fa-envelope',
    description: 'Доставка по всей России. Отслеживание по трек-номеру.',
    price: 'от 350 ₽'
  },
  {
    id: 4,
    name: 'СДЭК',
    icon: 'fas fa-box',
    description: 'Быстрая доставка в пункты выдачи и постаматы.',
    price: 'от 400 ₽'
  }
]

const paymentMethods = [
  {
    id: 1,
    name: 'Банковская карта онлайн',
    icon: 'fas fa-credit-card',
    description: 'Visa, Mastercard, МИР. Безопасная оплата на сайте.'
  },
  {
    id: 2,
    name: 'Наличные при получении',
    icon: 'fas fa-money-bill-wave',
    description: 'Оплата курьеру или в пункте самовывоза.'
  },
  {
    id: 3,
    name: 'Бонусный счёт',
    icon: 'fas fa-coins',
    description: 'Оплата бонусами до 50% от суммы заказа.'
  }
]

const deliveryZones = [
  { city: 'Москва', time: '1-2 дня', price: '300 ₽' },
  { city: 'Санкт-Петербург', time: '2-3 дня', price: '350 ₽' },
  { city: 'Другие регионы', time: '3-7 дней', price: 'от 400 ₽' }
]

const returnRulesLeft = [
  'Товар должен быть в оригинальной упаковке',
  'Сохранены все бирки и этикетки',
  'Товар не был в использовании'
]

const returnRulesRight = [
  'Возврат осуществляется за счёт покупателя',
  'Деньги возвращаются в течение 5-7 дней',
  'Для возврата свяжитесь с поддержкой'
]
</script>