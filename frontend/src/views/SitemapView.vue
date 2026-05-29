<template>
    <div>
      <h1 class="page-title">Карта сайта</h1>
      
      <div class="bg-white rounded-xl p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="section in sitemap" :key="section.title">
            <h3 class="text-[#5e1104] text-lg mb-4 pb-2 border-b border-[#7f8330]/20">
              {{ section.title }}
            </h3>
            <ul class="space-y-2">
              <li v-for="link in section.links" :key="link.path">
                <router-link 
                  :to="link.path"
                  class="text-[#6c6456] hover:text-[#7f8330] transition-colors flex items-center gap-2"
                >
                  <i :class="link.icon" class="text-sm w-5"></i>
                  {{ link.name }}
                </router-link>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  const sitemap = [
    {
      title: 'Основное',
      links: [
        { path: '/', name: 'Главная', icon: 'fas fa-home' },
        { path: '/catalog', name: 'Каталог', icon: 'fas fa-book' },
        { path: '/collections', name: 'Коллекции', icon: 'fas fa-feather-alt' },
        { path: '/bookcrossing', name: 'Буккроссинг', icon: 'fas fa-exchange-alt' },
        { path: '/author-meetings', name: 'Авторские встречи', icon: 'fas fa-calendar-alt' },
        { path: '/about', name: 'О нас', icon: 'fas fa-info-circle' }
      ]
    },
    {
      title: 'Личный кабинет',
      links: [
        { path: '/account', name: 'Профиль', icon: 'fas fa-user' },
        { path: '/orders', name: 'Мои заказы', icon: 'fas fa-shopping-bag' },
        { path: '/favorites', name: 'Избранное', icon: 'fas fa-heart' },
        { path: '/cart', name: 'Корзина', icon: 'fas fa-shopping-basket' },
      ]
    },
    {
      title: 'Информация',
      links: [
        { path: '/delivery-payment', name: 'Доставка и оплата', icon: 'fas fa-credit-card' },
        { path: '/contacts', name: 'Контакты', icon: 'fas fa-phone' },
        { path: '/faq', name: 'Вопросы и ответы', icon: 'fas fa-question-circle' },
        { path: '/sitemap', name: 'Карта сайта', icon: 'fas fa-sitemap' }
      ]
    },
  ]
  </script>