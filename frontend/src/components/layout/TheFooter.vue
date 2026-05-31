<template>
  <footer class="bg-[#faf6f0] mt-24 pt-16 pb-8 border-t border-[#d4c5b0]/40 relative">
    <!-- Декоративная полоса сверху -->
    <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-[#c8a87c] via-[#8b7355] to-[#c8a87c]"></div>
    
    <div class="container mx-auto max-w-7xl px-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12 mb-16">
        <!-- Колонка 1: О магазине -->
        <div>
          <h3 class="font-['Montserrat'] text-xl text-[#2c2c2c] mb-6 pb-4 relative after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-12 after:h-0.5 after:bg-[#c8a87c]">
            Леди Bag
          </h3>
          <p class="text-[#8b7355] text-sm leading-relaxed mb-5">
            Стильные сумки и аксессуары для тех, кто ценит качество, элегантность и индивидуальность.
          </p>
          <div class="flex gap-5">
            <a href="#" v-for="social in socials" :key="social.icon" :class="social.color" class="text-2xl hover:text-[#c8a87c] transition-all hover:-translate-y-1">
              <i :class="social.icon"></i>
            </a>
          </div>
        </div>

        <!-- Колонка 2: Каталог -->
        <div>
          <h3 class="font-['Montserrat'] text-xl text-[#2c2c2c] mb-6 pb-4 relative after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-12 after:h-0.5 after:bg-[#c8a87c]">
            Каталог
          </h3>
          <ul class="space-y-3">
            <li v-for="item in sections" :key="item.path">
              <router-link :to="item.path" class="text-[#8b7355] hover:text-[#c8a87c] transition-all hover:translate-x-1 inline-flex items-center gap-2 text-sm">
                <i :class="item.icon" class="text-sm w-4"></i>
                {{ item.name }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Колонка 3: Помощь -->
        <div>
          <h3 class="font-['Montserrat'] text-xl text-[#2c2c2c] mb-6 pb-4 relative after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-12 after:h-0.5 after:bg-[#c8a87c]">
            Помощь
          </h3>
          <ul class="space-y-3">
            <li v-for="item in help" :key="item.path">
              <router-link :to="item.path" class="text-[#8b7355] hover:text-[#c8a87c] transition-all hover:translate-x-1 inline-flex items-center gap-2 text-sm">
                <i :class="item.icon" class="text-sm w-4"></i>
                {{ item.name }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Колонка 4: Контакты -->
        <div>
          <h3 class="font-['Montserrat'] text-xl text-[#2c2c2c] mb-6 pb-4 relative after:content-[''] after:absolute after:bottom-0 after:left-0 after:w-12 after:h-0.5 after:bg-[#c8a87c]">
            Контакты
          </h3>
          <ul class="space-y-3">
            <li v-for="item in contacts" :key="item.text">
              <a :href="item.href" class="text-[#8b7355] hover:text-[#c8a87c] transition-all hover:translate-x-1 inline-flex items-center gap-2 text-sm">
                <i :class="item.icon" class="text-sm w-4"></i>
                {{ item.text }}
              </a>
            </li>
          </ul>
          <!-- Дополнительная информация о режиме работы -->
          <div class="mt-6 pt-4 border-t border-[#d4c5b0]/30">
            <p class="text-[#8b7355] text-xs flex items-center gap-2">
              <i class="fas fa-clock text-[#c8a87c]"></i>
              Пн-Вс: 10:00 - 21:00
            </p>
          </div>
        </div>
      </div>

      <!-- Нижняя часть футера -->
      <div class="pt-8 border-t border-[#d4c5b0]/30 text-center text-xs text-[#8b7355] space-y-2">
        <p>© {{ new Date().getFullYear() }} Леди Bag. Все права защищены.</p>
        <p class="text-[#b8a28c]">Стиль, который всегда с вами</p>
      </div>
    </div>
  </footer>
</template>

<script setup>

const sections = [
  { path: '/catalog', name: 'Сумки', icon: 'fas fa-shopping-bag' },
  { path: '/collections', name: 'Коллекции', icon: 'fas fa-layer-group' },
]

const help = [
  { path: '/delivery-payment', name: 'Доставка и оплата', icon: 'fas fa-truck' },
  { path: '/faq', name: 'Вопросы и ответы', icon: 'fas fa-question-circle' },
  { path: '/contacts', name: 'Контакты', icon: 'fas fa-phone-alt' },
]

const contacts = [
  { href: '#', text: 'г. Иркутск, Ленина 5а', icon: 'fas fa-map-marker-alt' },
  { href: 'tel:74951234567', text: '+7 (495) 123-45-67', icon: 'fas fa-phone' },
  { href: 'mailto:info@ladibag.ru', text: 'info@ladibag.ru', icon: 'fas fa-envelope' }
]
</script>