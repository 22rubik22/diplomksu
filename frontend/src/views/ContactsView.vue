<template>
  <div>
    <h1 class="page-title">Контакты</h1>
    
    <div class="bg-white rounded-xl p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Контактная информация -->
        <div>
          <h3 class="text-[#5e1104] text-xl mb-6">Как нас найти</h3>
          
          <div class="space-y-4">
            <div class="flex items-start gap-4">
              <i class="fas fa-map-marker-alt text-xl text-[#7f8330] w-6"></i>
              <div>
                <p class="font-medium text-[#5e1104]">Адрес</p>
                <p class="text-[#6c6456]">Иркутск, улица Ленина 5А</p>
              </div>
            </div>
            
            <div class="flex items-start gap-4">
              <i class="fas fa-phone text-xl text-[#7f8330] w-6"></i>
              <div>
                <p class="font-medium text-[#5e1104]">Телефон</p>
                <a href="tel:88122345678" class="text-[#6c6456] hover:text-[#7f8330]">8 (812) 234-56-78</a>
              </div>
            </div>
            
            <div class="flex items-start gap-4">
              <i class="fas fa-envelope text-xl text-[#7f8330] w-6"></i>
              <div>
                <p class="font-medium text-[#5e1104]">Email</p>
                <a href="mailto:ivy@books.ru" class="text-[#6c6456] hover:text-[#7f8330]">ivy@books.ru</a>
              </div>
            </div>
            
            <div class="flex items-start gap-4">
              <i class="fas fa-clock text-xl text-[#7f8330] w-6"></i>
              <div>
                <p class="font-medium text-[#5e1104]">Часы работы</p>
                <p class="text-[#6c6456]">Пн-Сб: 11:00-21:00</p>
                <p class="text-[#6c6456]">Вс: 12:00-20:00</p>
              </div>
            </div>
          </div>

          <!-- Социальные сети -->
          <div class="mt-8">
            <h4 class="text-[#5e1104] mb-4">Мы в соцсетях</h4>
            <div class="flex gap-4">
              <a 
                v-for="social in socials" 
                :key="social.name"
                :href="social.url"
                class="w-12 h-12 border border-[#7f8330] rounded-lg flex items-center justify-center text-[#7f8330] hover:bg-[#7f8330] hover:text-white transition-colors"
                target="_blank"
              >
                <i :class="social.icon" class="text-xl"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- Карта -->
        <div>
          <div class="bg-[#7f8330]/5 h-[500px] rounded-xl overflow-hidden">
            <iframe 
              src="https://yandex.ru/map-widget/v1/?ll=104.2805%2C52.2895&mode=search&text=%D0%98%D1%80%D0%BA%D1%83%D1%82%D1%81%D0%BA%2C%20%D1%83%D0%BB%D0%B8%D1%86%D0%B0%20%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B0%205%D0%90&pt=104.2805%2C52.2895%2Cpmrdm&z=18" 
              width="100%" 
              height="500" 
              frameborder="0" 
              allowfullscreen="true"
              style="position: relative; border: 0; border-radius: 0.75rem;"
            ></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const socials = [
  { name: 'Pinterest', icon: 'fab fa-pinterest', url: 'https://pinterest.com' },
  { name: 'Instagram', icon: 'fab fa-instagram', url: 'https://instagram.com' },
  { name: 'VK', icon: 'fab fa-vk', url: 'https://vk.com' }
]

const feedback = ref({
  name: '',
  email: '',
  message: ''
})

const sendMessage = () => {
  console.log('Отправка сообщения:', feedback.value)
  alert('Сообщение отправлено! Мы ответим вам в ближайшее время.')
  feedback.value = { name: '', email: '', message: '' }
}
</script>

<style scoped>
.page-title {
  font-size: 2rem;
  font-weight: 700;
  color: #5e1104;
  margin-bottom: 1.5rem;
  position: relative;
  display: inline-block;
}

.page-title::after {
  content: '';
  position: absolute;
  bottom: -8px;
  left: 0;
  width: 60%;
  height: 3px;
  background: linear-gradient(90deg, #7f8330, #f3d8ce);
  border-radius: 2px;
}
</style>