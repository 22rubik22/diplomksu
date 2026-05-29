<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Hero секция -->
    <div class="text-center mb-16">
      <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-3 font-light">About</p>
      <h1 class="text-3xl sm:text-4xl font-light text-black tracking-tight">О нас</h1>
      <div class="w-12 h-px bg-black/20 mx-auto mt-4"></div>
    </div>

    <!-- Основной текст -->
    <div class="max-w-3xl mx-auto text-center mb-20">
      <p class="text-black/60 text-sm sm:text-base leading-relaxed font-light">
        «Леди Bag» — это пространство для тех, кто видит в аксессуарах не просто дополнение, 
        а способ выразить свою индивидуальность. Мы создаём коллекции, которые сочетают 
        в себе элегантность, качество и вневременной стиль.
      </p>
    </div>

    <!-- Три колонки с философией -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20">
      <div class="text-center px-4">
        <div class="flex justify-center mb-6">
          <div class="w-12 h-px bg-black/20"></div>
        </div>
        <h3 class="text-black text-sm tracking-[0.2em] uppercase mb-3 font-light">Качество</h3>
        <p class="text-black/50 text-sm font-light leading-relaxed">
          Используем только натуральные материалы и проверяем каждую деталь перед отправкой
        </p>
      </div>
      
      <div class="text-center px-4">
        <div class="flex justify-center mb-6">
          <div class="w-12 h-px bg-black/20"></div>
        </div>
        <h3 class="text-black text-sm tracking-[0.2em] uppercase mb-3 font-light">Стиль</h3>
        <p class="text-black/50 text-sm font-light leading-relaxed">
          Каждая модель — это результат работы дизайнеров, вдохновлённых мировыми трендами
        </p>
      </div>
      
      <div class="text-center px-4">
        <div class="flex justify-center mb-6">
          <div class="w-12 h-px bg-black/20"></div>
        </div>
        <h3 class="text-black text-sm tracking-[0.2em] uppercase mb-3 font-light">Забота</h3>
        <p class="text-black/50 text-sm font-light leading-relaxed">
          Индивидуальный подход к каждому клиенту и помощь с выбором идеального аксессуара
        </p>
      </div>
    </div>

    <!-- Наша команда -->
    <div class="mb-20">
      <div class="text-center mb-12">
        <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-3 font-light">Team</p>
        <h2 class="text-xl sm:text-2xl font-light text-black tracking-tight">Команда</h2>
        <div class="w-8 h-px bg-black/20 mx-auto mt-3"></div>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <div v-for="member in team" :key="member.name" class="text-center">
          <div class="aspect-square bg-[#f8f8f8] mb-4 flex items-center justify-center overflow-hidden">
            <i class="fas fa-user text-4xl text-black/20"></i>
          </div>
          <h3 class="text-black text-sm font-light tracking-wide">{{ member.name }}</h3>
          <p class="text-black/40 text-xs font-light mt-1">{{ member.position }}</p>
        </div>
      </div>
    </div>

    <!-- Наши преимущества -->
    <div class="border-t border-black/5 pt-16">
      <div class="text-center mb-12">
        <p class="text-[10px] tracking-[0.3em] uppercase text-black/40 mb-3 font-light">Why choose us</p>
        <h2 class="text-xl sm:text-2xl font-light text-black tracking-tight">Почему мы</h2>
        <div class="w-8 h-px bg-black/20 mx-auto mt-3"></div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 max-w-3xl mx-auto">
        <div class="flex items-start gap-4">
          <i class="fas fa-gem text-sm text-black/30 mt-0.5"></i>
          <div>
            <h3 class="text-black text-xs tracking-[0.2em] uppercase mb-1 font-light">Оригинальные бренды</h3>
            <p class="text-black/40 text-xs font-light">Только официальные поставщики и подлинные изделия</p>
          </div>
        </div>
        
        <div class="flex items-start gap-4">
          <i class="fas fa-truck text-sm text-black/30 mt-0.5"></i>
          <div>
            <h3 class="text-black text-xs tracking-[0.2em] uppercase mb-1 font-light">Быстрая доставка</h3>
            <p class="text-black/40 text-xs font-light">По всей России от 2 до 7 дней</p>
          </div>
        </div>
        
        <div class="flex items-start gap-4">
          <i class="fas fa-undo-alt text-sm text-black/30 mt-0.5"></i>
          <div>
            <h3 class="text-black text-xs tracking-[0.2em] uppercase mb-1 font-light">Легкий возврат</h3>
            <p class="text-black/40 text-xs font-light">14 дней на примерку и возврат</p>
          </div>
        </div>
        
        <div class="flex items-start gap-4">
          <i class="fas fa-headset text-sm text-black/30 mt-0.5"></i>
          <div>
            <h3 class="text-black text-xs tracking-[0.2em] uppercase mb-1 font-light">Поддержка 24/7</h3>
            <p class="text-black/40 text-xs font-light">Всегда готовы помочь с выбором</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Контакты -->
    <div class="mt-20 pt-16 border-t border-black/5">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
        <div>
          <i class="fas fa-map-marker-alt text-black/30 text-lg mb-3"></i>
          <p class="text-black/60 text-xs font-light">г. Москва, Кутузовский пр-т, 36</p>
        </div>
        <div>
          <i class="fas fa-phone-alt text-black/30 text-lg mb-3"></i>
          <p class="text-black/60 text-xs font-light">+7 (495) 123-45-67</p>
        </div>
        <div>
          <i class="fas fa-envelope text-black/30 text-lg mb-3"></i>
          <p class="text-black/60 text-xs font-light">info@ladibag.ru</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const team = [
  { name: 'Екатерина Белова', position: 'Основатель' },
  { name: 'Анна Соколова', position: 'Дизайнер' },
  { name: 'Мария Волкова', position: 'Куратор коллекций' },
  { name: 'Дмитрий Петров', position: 'Руководитель отдела продаж' }
]
</script>