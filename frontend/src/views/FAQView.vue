<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Хлебные крошки -->
    <div class="mb-8 text-[10px] tracking-[0.2em] uppercase text-black/30">
      <span class="hover:text-black/60 cursor-pointer" @click="$router.push('/')">Главная</span>
      <span class="mx-2">/</span>
      <span class="text-black/60">Часто задаваемые вопросы</span>
    </div>

    <!-- Hero секция -->
    <div class="text-center mb-12">
      <p class="text-[10px] tracking-[0.3em] uppercase text-[#c8a87c] mb-3 font-light">FAQ</p>
      <h1 class="text-3xl sm:text-4xl font-light text-[#2c2c2c] tracking-tight">Часто задаваемые вопросы</h1>
      <div class="w-12 h-px bg-[#c8a87c]/30 mx-auto mt-4"></div>
      <p class="text-[#8b7355] text-sm font-light mt-4 max-w-2xl mx-auto">
        Здесь вы найдете ответы на самые популярные вопросы о нашем магазине, доставке и товарах
      </p>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Левая колонка: категории вопросов -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-[#e8e0d8] sticky top-24">
          <h3 class="text-sm font-medium text-[#2c2c2c] mb-4">Разделы</h3>
          <div class="space-y-4">
            <div 
              v-for="category in categories" 
              :key="category.title"
              class="border-b border-[#e8e0d8] pb-3 last:border-0 last:pb-0"
            >
              <div class="flex items-center gap-3 mb-2">
                <i :class="category.icon" class="text-[#c8a87c] text-sm w-5"></i>
                <h4 class="text-xs font-medium text-[#2c2c2c]">{{ category.title }}</h4>
              </div>
              <ul class="space-y-1 ml-8">
                <li 
                  v-for="link in category.links" 
                  :key="link"
                  class="text-[11px] text-[#8b7355] hover:text-[#c8a87c] cursor-pointer transition-colors"
                  @click="scrollToQuestion(link)"
                >
                  {{ link }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Правая колонка: вопросы и ответы -->
      <div class="lg:col-span-2">
        <div class="space-y-4">
          <div 
            v-for="(faq, index) in faqs" 
            :key="index"
            class="bg-white rounded-2xl border border-[#e8e0d8] overflow-hidden transition-all"
            :class="{ 'shadow-md': openItems.includes(index) }"
          >
            <button 
              @click="toggleFaq(index)"
              class="w-full text-left p-5 bg-white hover:bg-[#faf8f5] transition-colors flex justify-between items-center group"
            >
              <div class="flex items-start gap-3">
                <span class="text-[#c8a87c] font-medium text-sm mt-0.5">0{{ index + 1 }}.</span>
                <span class="font-light text-[#2c2c2c] text-base">{{ faq.question }}</span>
              </div>
              <i 
                class="fas fa-chevron-down text-[#c8a87c] text-xs transition-transform duration-300 group-hover:translate-y-0.5"
                :class="{ 'rotate-180': openItems.includes(index) }"
              ></i>
            </button>
            
            <div 
              v-show="openItems.includes(index)"
              class="p-5 pt-0 text-[#8b7355] text-sm font-light leading-relaxed border-t border-[#e8e0d8] bg-[#faf8f5]"
            >
              {{ faq.answer }}
            </div>
          </div>
        </div>

        <!-- Если не нашли ответ -->
        <div class="mt-12 text-center p-8 bg-[#faf8f5] rounded-2xl border border-[#e8e0d8]">
          <div class="w-16 h-16 rounded-full bg-[#c8a87c]/10 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-question-circle text-2xl text-[#c8a87c]"></i>
          </div>
          <h3 class="text-[#2c2c2c] text-lg font-light mb-2">Не нашли ответ на свой вопрос?</h3>
          <p class="text-[#8b7355] text-sm mb-6">Напишите нам, и мы поможем вам в течение 24 часов</p>
          <button 
            @click="$router.push('/contacts')"
            class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#c8a87c] text-white rounded-xl text-xs tracking-[0.2em] uppercase font-light hover:bg-[#b89a6e] transition-all"
          >
            Связаться с нами
            <i class="fas fa-arrow-right text-[10px]"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const openItems = ref([0]) // По умолчанию открыт первый вопрос

const faqs = [
  {
    question: 'Как оформить заказ?',
    answer: 'Выберите понравившуюся сумку или аксессуар, добавьте товар в корзину, затем перейдите к оформлению. Укажите данные для доставки, выберите удобный способ оплаты и подтвердите заказ. После оформления вы получите письмо с деталями на указанный email.'
  },
  {
    question: 'Какие способы доставки доступны?',
    answer: 'Мы предлагаем несколько способов доставки: курьерская доставка по Санкт-Петербургу (300 ₽), самовывоз из нашего шоурума (бесплатно) и почтовая доставка по России (от 250 ₽). Точная стоимость и сроки доставки рассчитываются при оформлении заказа.'
  },
  {
    question: 'Сколько времени занимает доставка?',
    answer: 'По Санкт-Петербургу доставка занимает 1-2 рабочих дня. По России срок доставки зависит от региона и выбранного способа: от 3 до 14 рабочих дней. Отслеживать статус заказа можно в личном кабинете.'
  },
  {
    question: 'Можно ли вернуть или обменять товар?',
    answer: 'Да, мы принимаем возврат и обмен товаров надлежащего качества в течение 14 дней с момента получения при условии сохранения товарного вида, бирок и упаковки. Возврат денежных средств осуществляется в течение 10 рабочих дней.'
  },
  {
    question: 'Как выбрать правильный размер сумки?',
    answer: 'В карточке каждого товара есть подробная таблица размеров. Мы рекомендуем ориентироваться на указанные параметры (длина, высота, ширина). Если у вас остались вопросы, свяжитесь с нашими консультантами — они помогут подобрать идеальный размер.'
  },
  {
    question: 'Из каких материалов изготовлены сумки?',
    answer: 'В нашем ассортименте представлены сумки из натуральной и экокожи, качественного текстиля и других материалов. Информация о составе указана в характеристиках товара. Все материалы проходят проверку качества.'
  },
  {
    question: 'Есть ли скидки для постоянных покупателей?',
    answer: 'Да, у нас действует бонусная программа. За каждый заказ вы получаете 5% от стоимости покупки бонусами, которые можно использовать для оплаты следующих заказов (до 50% от суммы). Также мы проводим сезонные распродажи и акции — подпишитесь на рассылку, чтобы не пропустить!'
  },
  {
    question: 'Как ухаживать за сумкой?',
    answer: 'Рекомендации по уходу зависят от материала. Для кожаных изделий используйте специальные средства для чистки и водоотталкивающие пропитки. Текстильные сумки можно чистить мягкой щеткой или деликатной стиркой (следуйте инструкции на бирке).'
  },
  {
    question: 'Работаете ли вы с оптовыми заказами?',
    answer: 'Да, мы предлагаем специальные условия для оптовых покупателей. Для обсуждения деталей свяжитесь с нами по email: wholesale@store.ru или через форму обратной связи в разделе "Контакты".'
  }
]

const categories = [
  {
    title: 'Заказы и доставка',
    icon: 'fas fa-truck',
    links: ['Как оформить заказ?', 'Какие способы доставки доступны?', 'Сколько времени занимает доставка?']
  },
  {
    title: 'Возврат и обмен',
    icon: 'fas fa-undo-alt',
    links: ['Можно ли вернуть или обменять товар?']
  },
  {
    title: 'Товары',
    icon: 'fas fa-shopping-bag',
    links: ['Как выбрать правильный размер сумки?', 'Из каких материалов изготовлены сумки?', 'Как ухаживать за сумкой?']
  },
  {
    title: 'Бонусы и скидки',
    icon: 'fas fa-gift',
    links: ['Есть ли скидки для постоянных покупателей?']
  },
  {
    title: 'Оптовым клиентам',
    icon: 'fas fa-building',
    links: ['Работаете ли вы с оптовыми заказами?']
  }
]

const toggleFaq = (index) => {
  if (openItems.value.includes(index)) {
    openItems.value = openItems.value.filter(i => i !== index)
  } else {
    openItems.value.push(index)
  }
}

// Функция для прокрутки к вопросу
const scrollToQuestion = (questionText) => {
  const index = faqs.findIndex(faq => faq.question === questionText)
  if (index !== -1) {
    if (!openItems.value.includes(index)) {
      openItems.value.push(index)
    }
    // Ждем рендера DOM и плавно скроллим
    setTimeout(() => {
      const elements = document.querySelectorAll('.bg-white.rounded-2xl.border')
      if (elements[index]) {
        elements[index].scrollIntoView({ behavior: 'smooth', block: 'start' })
      }
    }, 100)
  }
}
</script>

<style scoped>
/* Дополнительные стили при необходимости */
</style>