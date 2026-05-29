<template>
    <div>
      <h1 class="page-title">Часто задаваемые вопросы</h1>
      
      <div class="bg-white rounded-xl p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
        <div class="space-y-4">
          <div 
            v-for="(faq, index) in faqs" 
            :key="index"
            class="border border-[#7f8330]/10 rounded-lg overflow-hidden"
          >
            <button 
              @click="toggleFaq(index)"
              class="w-full text-left p-5 bg-[#7f8330]/5 hover:bg-[#7f8330]/10 transition-colors flex justify-between items-center"
            >
              <span class="font-medium text-[#5e1104]">{{ faq.question }}</span>
              <i 
                class="fas fa-chevron-down text-[#7f8330] transition-transform"
                :class="{ 'rotate-180': openItems.includes(index) }"
              ></i>
            </button>
            
            <div 
              v-show="openItems.includes(index)"
              class="p-5 text-[#6c6456] leading-relaxed"
            >
              {{ faq.answer }}
            </div>
          </div>
        </div>
  
        
  
        <!-- Если не нашли ответ -->
        <div class="mt-12 text-center p-8 bg-[#7f8330]/5 rounded-xl">
          <i class="fas fa-question-circle text-4xl text-[#7f8330] mb-4"></i>
          <h3 class="text-[#5e1104] text-xl mb-2">Не нашли ответ на свой вопрос?</h3>
          <p class="text-[#6c6456] mb-4">Напишите нам, и мы поможем</p>
          <router-link to="/contacts" class="btn-primary px-8 py-3">
            Связаться с нами
          </router-link>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  
  const openItems = ref([])
  
  const faqs = [
    {
      question: 'Как оформить заказ?',
      answer: 'Выберите понравившиеся книги, добавьте их в корзину, затем перейдите к оформлению. Укажите данные для доставки и выберите удобный способ оплаты. После подтверждения заказа вы получите письмо с деталями на email.'
    },
    {
      question: 'Какие сроки доставки?',
      answer: 'По Санкт-Петербургу доставка занимает 1-2 дня, по России — от 5 дней в зависимости от региона и выбранного способа доставки. Точные сроки будут указаны при оформлении заказа.'
    },
    {
      question: 'Можно ли вернуть книгу?',
      answer: 'Да, мы принимаем возврат в течение 14 дней при сохранении товарного вида и упаковки. Для букинистических изданий действуют особые условия — пожалуйста, ознакомьтесь с разделом "Возврат" или свяжитесь с нами для уточнения.'
    },
    {
      question: 'Как участвовать в буккроссинге?',
      answer: 'Принесите книгу в хорошем состоянии в наш магазин, и вы сможете бесплатно выбрать любую книгу из доступных для обмена. Все книги проходят проверку нашими специалистами.'
    },
    {
      question: 'Проводятся ли авторские встречи?',
      answer: 'Да, мы регулярно проводим встречи с современными авторами, презентации книг и лекции. Следите за анонсами в разделе "Авторские встречи" и в наших социальных сетях.'
    },
    {
      question: 'Работаете ли вы с оптовыми заказами?',
      answer: 'Да, мы предлагаем специальные условия для библиотек, школ и корпоративных клиентов. Для обсуждения оптовых заказов свяжитесь с нами по email ivy@books.ru'
    }
  ]
  
  const categories = [
    {
      title: 'Доставка и оплата',
      icon: 'fas fa-truck',
      links: ['Способы доставки', 'Способы оплаты', 'Сроки доставки', 'Отследить заказ']
    },
    {
      title: 'Товары и заказы',
      icon: 'fas fa-box',
      links: ['Как заказать', 'Отмена заказа', 'Возврат товара', 'Подарочные сертификаты']
    },
    {
      title: 'Клиентам',
      icon: 'fas fa-users',
      links: ['Бонусная программа', 'Подарочные карты', 'Оптовым клиентам', 'Сотрудничество']
    }
  ]
  
  const toggleFaq = (index) => {
    if (openItems.value.includes(index)) {
      openItems.value = openItems.value.filter(i => i !== index)
    } else {
      openItems.value.push(index)
    }
  }
  </script>