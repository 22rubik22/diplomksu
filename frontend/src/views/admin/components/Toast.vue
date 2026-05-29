<template>
    <transition 
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-2"
    >
      <div 
        v-if="toast.show" 
        class="fixed bottom-4 right-4 z-50"
      >
        <div class="px-5 py-3 rounded-xl shadow-lg text-white flex items-center gap-2" :class="{
          'bg-gradient-to-r from-green-500 to-green-600': toast.type === 'success',
          'bg-gradient-to-r from-red-500 to-red-600': toast.type === 'error',
          'bg-gradient-to-r from-yellow-500 to-yellow-600': toast.type === 'warning',
          'bg-gradient-to-r from-blue-500 to-blue-600': toast.type === 'info'
        }">
          <i :class="{
            'fas fa-check-circle': toast.type === 'success',
            'fas fa-exclamation-circle': toast.type === 'error',
            'fas fa-exclamation-triangle': toast.type === 'warning',
            'fas fa-info-circle': toast.type === 'info'
          }"></i>
          {{ toast.message }}
        </div>
      </div>
    </transition>
  </template>
  
  <script setup>
  defineProps({
    toast: {
      type: Object,
      required: true
    }
  })
  </script>