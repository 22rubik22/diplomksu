<template>
  <div class="min-h-screen flex flex-col relative">
    <!-- Декоративный плющ -->
    <div class="ivy-left"></div>
    <div class="ivy-right"></div>
    
    <!-- Шапка -->
    <TheHeader />
    
    <!-- Основной контент с анимацией перехода -->
    <main class="flex-1 container mx-auto max-w-7xl px-4 md:px-8 py-16">
      <router-view v-slot="{ Component }">
        <transition name="page" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
    
    <!-- Футер -->
    <TheFooter />
  </div>
</template>

<script setup>
import TheHeader from '@/components/layout/TheHeader.vue'
import TheFooter from '@/components/layout/TheFooter.vue'
import { onMounted } from 'vue'
import { useCartStore } from '@/stores/cart'

const cartStore = useCartStore()

onMounted(() => {
  // Загружаем корзину при старте
  cartStore.loadCart()
})
</script>

<style>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.4s, transform 0.4s;
}

.page-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.page-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}
</style>