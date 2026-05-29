<template>
    <div>
      <h1 class="page-title">Букинистика</h1>
      
      <div class="bg-white rounded-xl p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
        <p class="text-lg text-[#6c6456] mb-8">
          Редкие издания, прижизненные публикации, книги с историей
        </p>
        
        <!-- Фильтры -->
        <div class="flex flex-wrap gap-4 mb-8">
          <select v-model="eraFilter" class="px-4 py-2 border border-[#7f8330]/30 rounded-lg">
            <option value="">Все эпохи</option>
            <option value="xix">XIX век</option>
            <option value="xx">XX век</option>
            <option value="modern">Современные редкости</option>
          </select>
          
          <select v-model="sortBy" class="px-4 py-2 border border-[#7f8330]/30 rounded-lg">
            <option value="price-asc">По цене (возрастание)</option>
            <option value="price-desc">По цене (убывание)</option>
            <option value="year-desc">По году (новые)</option>
            <option value="year-asc">По году (старые)</option>
          </select>
        </div>
        
        <!-- Сетка книг -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div 
            v-for="book in filteredBooks" 
            :key="book.id"
            class="border border-[#7f8330]/10 rounded-xl overflow-hidden hover:shadow-lg transition-shadow"
          >
            <div class="h-48 bg-gradient-to-br from-[#f3d8ce] to-[#f8e6df] flex items-center justify-center">
              <i class="fas fa-book-open text-5xl text-[#7f8330]/30"></i>
            </div>
            
            <div class="p-5">
              <h3 class="text-[#5e1104] font-['Playfair_Display'] text-xl mb-2">{{ book.title }}</h3>
              <p class="text-[#7f8330] text-sm mb-1">{{ book.author }}</p>
              <p class="text-[#6c6456] text-sm mb-2">{{ book.year }} • {{ book.publisher }}</p>
              
              <div class="flex justify-between items-center mt-4">
                <span class="text-xl text-[#5e1104] font-medium">{{ formatPrice(book.price) }} ₽</span>
                <span class="bg-[#b59b6d] text-white px-2 py-1 rounded text-xs">
                  Уникальный экземпляр
                </span>
              </div>
              
              <button @click="goToProduct(book.id)" class="w-full mt-4 btn py-2">
                Подробнее
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed } from 'vue'
  import { useRouter } from 'vue-router'
  
  const router = useRouter()
  const eraFilter = ref('')
  const sortBy = ref('year-desc')
  
  const books = ref([
    {
      id: 1,
      title: 'Собрание сочинений А.С. Пушкина',
      author: 'А.С. Пушкин',
      year: '1899',
      publisher: 'Издание А.Ф. Маркса',
      price: 12500,
      era: 'xix'
    },
    {
      id: 2,
      title: 'Стихотворения',
      author: 'А.А. Блок',
      year: '1916',
      publisher: 'Мусагет',
      price: 8900,
      era: 'xx'
    },
    {
      id: 3,
      title: 'Доктор Живаго',
      author: 'Б.Л. Пастернак',
      year: '1958',
      publisher: 'Feltrinelli',
      price: 15000,
      era: 'xx'
    },
    {
      id: 4,
      title: 'Сказки',
      author: 'А.С. Пушкин',
      year: '1903',
      publisher: 'Товарищество Р. Голике и А. Вильборг',
      price: 8500,
      era: 'xix'
    }
  ])
  
  const filteredBooks = computed(() => {
    let filtered = books.value
    
    if (eraFilter.value) {
      filtered = filtered.filter(book => book.era === eraFilter.value)
    }
    
    return filtered.sort((a, b) => {
      switch (sortBy.value) {
        case 'price-asc':
          return a.price - b.price
        case 'price-desc':
          return b.price - a.price
        case 'year-asc':
          return a.year - b.year
        case 'year-desc':
          return b.year - a.year
        default:
          return 0
      }
    })
  })
  
  const goToProduct = (id) => {
    router.push(`/product/${id}`)
  }
  
  const formatPrice = (price) => {
    return new Intl.NumberFormat('ru-RU').format(price)
  }
  </script>