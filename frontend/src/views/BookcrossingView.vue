<template>
  <div>
    <h1 class="page-title">Буккроссинг</h1>
    
    <div class="space-y-4 sm:space-y-6 md:space-y-8">
      <!-- Баннер -->
      <div class="bg-gradient-to-r from-[#7f8330]/20 to-[#5e1104]/20 rounded-xl p-6 sm:p-8 md:p-10 text-center">
        <i class="fas fa-exchange-alt text-4xl sm:text-5xl text-[#7f8330] mb-3 sm:mb-4"></i>
        <h2 class="text-2xl sm:text-3xl text-[#5e1104] font-['Playfair_Display'] mb-2 sm:mb-4">Освободи книги</h2>
        <p class="text-sm sm:text-base md:text-lg text-[#6c6456] max-w-2xl mx-auto mb-4 sm:mb-6 px-2">
          Принеси прочитанную книгу в наш магазин и выбери новую для чтения. Бесплатно и экологично.
        </p>
        <button @click="openAddBookModal" class="btn-primary px-6 sm:px-8 py-2 sm:py-3 text-sm sm:text-base w-full sm:w-auto" :disabled="!isAuthenticated">
          {{ isAuthenticated ? 'Участвовать' : 'Войдите, чтобы участвовать' }}
        </button>
      </div>
      
        <!-- Как это работает -->
        <div class="bg-white rounded-xl p-4 sm:p-6 md:p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
        <h3 class="text-[#5e1104] text-xl sm:text-2xl mb-4 sm:mb-6 text-center">Как это работает</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
          <div v-for="(step, index) in steps" :key="index" class="text-center">
            <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#7f8330] text-white rounded-full flex items-center justify-center mx-auto mb-2 sm:mb-4 text-sm sm:text-base">
              {{ index + 1 }}
            </div>
            <h4 class="text-[#5e1104] font-medium text-sm sm:text-base mb-1 sm:mb-2">{{ step.title }}</h4>
            <p class="text-xs sm:text-sm text-[#6c6456] px-2">{{ step.description }}</p>
          </div>
        </div>
      </div>
      
      <!-- Мои книги (если авторизован) -->
      <div v-if="isAuthenticated && (myBooks.length > 0 || myTakenBooks.length > 0)" class="bg-white rounded-xl p-4 sm:p-6 md:p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 sm:mb-6">
          <h3 class="text-[#5e1104] text-xl sm:text-2xl">Мои книги в буккроссинге</h3>
          <button @click="openAddBookModal" class="text-[#7f8330] hover:underline text-sm">
            + Добавить книгу
          </button>
        </div>
        
        <!-- Книги, которые я добавил -->
        <div v-if="myBooks.length">
          <h4 class="text-[#5e1104] text-base sm:text-lg mb-2 sm:mb-3">Мои добавленные книги</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-4 sm:mb-6">
            <div v-for="book in myBooks" :key="book.id" class="border border-[#7f8330]/10 rounded-lg p-3 sm:p-4">
              <div class="flex gap-3 sm:gap-4">
                <!-- Обложка книги -->
                <div class="w-16 h-20 sm:w-20 sm:h-24 bg-[#f3d8ce] rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                  <img 
                    v-if="book.image && book.image.trim() !== ''" 
                    :src="book.image" 
                    :alt="book.title" 
                    class="h-16 sm:h-20 object-contain"
                  >
                  <i v-else class="fas fa-book text-2xl sm:text-3xl text-[#7f8330]/30"></i>
                </div>
                
                <!-- Информация о книге -->
                <div class="flex-1 min-w-0">
                  <h5 class="font-medium text-[#5e1104] text-sm sm:text-base line-clamp-2">{{ book.title }}</h5>
                  <p class="text-xs sm:text-sm text-[#6c6456] line-clamp-1">{{ book.author }}</p>
                  <p v-if="book.genre" class="text-[10px] sm:text-xs text-[#7f8330] mt-1">
                    Жанр: {{ book.genre }}
                  </p>
                  <span :class="getStatusClass(book.status)" class="text-[10px] sm:text-xs px-2 py-0.5 sm:py-1 rounded-full mt-2 inline-block">
                    {{ getStatusText(book.status) }}
                  </span>
                </div>
                
                <!-- Кнопка удаления -->
                <i class="fas fa-trash-alt text-[#b59b6d] cursor-pointer hover:text-red-500 transition-colors flex-shrink-0 self-center" @click="deleteBook(book.id)"></i>
              </div>
              <p v-if="book.taken_by" class="text-[10px] sm:text-xs text-[#7f8330] mt-2 sm:mt-3">
                Взял: {{ book.taken_by.name }}
              </p>
            </div>
          </div>
        </div>
        
        <!-- Книги, которые я взял -->
        <div v-if="myTakenBooks.length">
          <h4 class="text-[#5e1104] text-base sm:text-lg mb-2 sm:mb-3">Книги, которые я взял</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
            <div v-for="book in myTakenBooks" :key="book.id" class="border border-[#7f8330]/10 rounded-lg p-3 sm:p-4">
              <div class="flex gap-3 sm:gap-4">
                <!-- Обложка книги -->
                <div class="w-16 h-20 sm:w-20 sm:h-24 bg-[#f3d8ce] rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
                  <img 
                    v-if="book.image && book.image.trim() !== ''" 
                    :src="book.image" 
                    :alt="book.title" 
                    class="h-16 sm:h-20 object-contain"
                  >
                  <i v-else class="fas fa-book text-2xl sm:text-3xl text-[#7f8330]/30"></i>
                </div>
                
                <!-- Информация о книге -->
                <div class="flex-1 min-w-0">
                  <h5 class="font-medium text-[#5e1104] text-sm sm:text-base line-clamp-2">{{ book.title }}</h5>
                  <p class="text-xs sm:text-sm text-[#6c6456] line-clamp-1">{{ book.author }}</p>
                  <p v-if="book.genre" class="text-[10px] sm:text-xs text-[#7f8330] mt-1">
                    Жанр: {{ book.genre }}
                  </p>
                  <span class="text-[10px] sm:text-xs bg-green-100 text-green-700 px-2 py-0.5 sm:py-1 rounded-full mt-2 inline-block">
                    Читаю
                  </span>
                  
                  <!-- Кнопка возврата -->
                  <button 
                    @click="returnBook(book.id)"
                    class="mt-2 sm:mt-3 text-xs sm:text-sm text-[#7f8330] hover:underline w-full sm:w-auto text-left"
                  >
                    Вернуть книгу
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Доступные книги -->
      <div class="bg-white rounded-xl p-4 sm:p-6 md:p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
          <h3 class="text-[#5e1104] text-xl sm:text-2xl">Доступные для обмена книги</h3>
          
          <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
            <div class="relative flex-1 sm:flex-none">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#b59b6d] text-xs sm:text-sm"></i>
              <input 
                type="text" 
                v-model="searchQuery"
                placeholder="Поиск..." 
                class="w-full pl-8 sm:pl-9 pr-3 sm:pr-4 py-1.5 sm:py-2 text-sm border border-[#7f8330]/30 rounded-lg"
                @input="onSearchInput"
              >
            </div>
            
            <!-- Фильтр по жанру -->
            <select 
              v-model="genreFilter"
              class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm border border-[#7f8330]/30 rounded-lg"
              @change="onGenreFilterChange"
            >
              <option value="">Все жанры</option>
              <option v-for="genre in genresList" :key="genre" :value="genre">
                {{ genre }}
              </option>
            </select>
            
            <select 
              v-model="statusFilter"
              class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm border border-[#7f8330]/30 rounded-lg"
              @change="loadBooks"
            >
              <option value="">Все статусы</option>
              <option value="available">Доступны</option>
              <option value="taken">Заняты</option>
            </select>
          </div>
        </div>
        
        <!-- Состояние загрузки -->
        <div v-if="loading" class="text-center py-8 sm:py-12">
          <i class="fas fa-spinner fa-spin text-2xl sm:text-3xl text-[#7f8330]"></i>
        </div>
        
        <!-- Список книг - мобильная версия 2 колонки -->
        <div v-else-if="books.length" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 sm:gap-3 md:gap-4">
          <div 
            v-for="book in books" 
            :key="book.id"
            class="text-center p-2 sm:p-3 border border-[#7f8330]/10 rounded-lg hover:shadow-md transition-shadow cursor-pointer"
            @click="showBookDetails(book)"
          >
            <div class="h-20 sm:h-24 bg-[#f3d8ce] rounded-lg mb-1 sm:mb-2 flex items-center justify-center overflow-hidden">
              <img v-if="book.image" :src="book.image" :alt="book.title" class="h-16 sm:h-20 object-contain">
              <i v-else class="fas fa-book text-2xl sm:text-3xl text-[#7f8330]/30"></i>
            </div>
            <h4 class="text-xs sm:text-sm font-medium text-[#5e1104] line-clamp-2 sm:line-clamp-1">{{ book.title }}</h4>
            <p class="text-[10px] sm:text-xs text-[#6c6456] line-clamp-1">{{ book.author }}</p>
            <p v-if="book.genre" class="text-[8px] sm:text-[10px] text-[#7f8330] mt-0.5 sm:mt-1 line-clamp-1">
              {{ book.genre }}
            </p>
            <span :class="getStatusClass(book.status)" class="text-[8px] sm:text-xs px-1.5 sm:px-2 py-0.5 rounded-full mt-1 sm:mt-2 inline-block">
              {{ getStatusText(book.status) }}
            </span>
          </div>
        </div>
        
        <!-- Нет книг -->
        <div v-else class="text-center py-8 sm:py-12">
          <i class="fas fa-book-open text-3xl sm:text-4xl text-[#7f8330]/30 mb-2 sm:mb-3"></i>
          <p class="text-sm sm:text-base text-[#6c6456]">Книги для обмена не найдены</p>
        </div>
        
        <!-- Пагинация -->
        <div v-if="pagination.last_page > 1" class="flex justify-center mt-6 sm:mt-8">
          <div class="flex gap-1 sm:gap-2">
            <button 
              @click="goToPage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-2 sm:px-3 py-1 text-sm border border-[#7f8330]/30 rounded-lg disabled:opacity-50"
            >
              &laquo;
            </button>
            <span class="px-2 sm:px-3 py-1 text-sm text-[#6c6456]">
              {{ pagination.current_page }} / {{ pagination.last_page }}
            </span>
            <button 
              @click="goToPage(pagination.current_page + 1)"
              :disabled="pagination.current_page === pagination.last_page"
              class="px-2 sm:px-3 py-1 text-sm border border-[#7f8330]/30 rounded-lg disabled:opacity-50"
            >
              &raquo;
            </button>
          </div>
        </div>
      </div>
      
      <!-- Правила -->
      <div class="bg-[#7f8330]/5 rounded-xl p-4 sm:p-6">
        <h3 class="text-[#5e1104] text-base sm:text-lg mb-3 sm:mb-4">Правила буккроссинга</h3>
        <ul class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm text-[#6c6456]">
          <li><i class="fas fa-check text-[#7f8330] mr-2 text-xs sm:text-sm"></i>Книги должны быть в хорошем состоянии</li>
          <li><i class="fas fa-check text-[#7f8330] mr-2 text-xs sm:text-sm"></i>Учебники и техническая литература не принимаются</li>
          <li><i class="fas fa-check text-[#7f8330] mr-2 text-xs sm:text-sm"></i>После прочтения книгу можно вернуть или оставить себе</li>
        </ul>
      </div>
    </div>
    
    <!-- Модальное окно добавления книги -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg sm:text-xl text-[#5e1104] font-['Playfair_Display'] mb-4">Добавить книгу в буккроссинг</h3>
        
        <form @submit.prevent="submitBook">
          <div class="mb-3 sm:mb-4">
            <label class="block text-xs sm:text-sm text-[#6c6456] mb-1 sm:mb-2">Название *</label>
            <input 
              type="text" 
              v-model="newBook.title"
              required
              class="w-full px-3 sm:px-4 py-2 text-sm border border-[#7f8330]/30 rounded-lg focus:outline-none focus:border-[#7f8330]"
            >
          </div>
          
          <div class="mb-3 sm:mb-4">
            <label class="block text-xs sm:text-sm text-[#6c6456] mb-1 sm:mb-2">Автор *</label>
            <input 
              type="text" 
              v-model="newBook.author"
              required
              class="w-full px-3 sm:px-4 py-2 text-sm border border-[#7f8330]/30 rounded-lg focus:outline-none focus:border-[#7f8330]"
            >
          </div>
          
          <div class="mb-3 sm:mb-4">
            <label class="block text-xs sm:text-sm text-[#6c6456] mb-1 sm:mb-2">Жанр</label>
            <div class="relative">
              <input 
                type="text" 
                v-model="newBook.genre"
                list="genres"
                placeholder="Начните вводить жанр..."
                class="w-full px-3 sm:px-4 py-2 text-sm border border-[#7f8330]/30 rounded-lg focus:outline-none focus:border-[#7f8330]"
              >
              <datalist id="genres">
                <option v-for="genre in allGenresList" :key="genre" :value="genre" />
              </datalist>
            </div>
            <p class="text-[10px] sm:text-xs text-[#6c6456] mt-1">Начните вводить — появятся подсказки из списка</p>
          </div>
          
          <div class="mb-3 sm:mb-4">
            <label class="block text-xs sm:text-sm text-[#6c6456] mb-1 sm:mb-2">Описание</label>
            <textarea 
              v-model="newBook.description"
              rows="3"
              class="w-full px-3 sm:px-4 py-2 text-sm border border-[#7f8330]/30 rounded-lg focus:outline-none focus:border-[#7f8330]"
            ></textarea>
          </div>
          
          <div class="mb-3 sm:mb-4">
            <label class="block text-xs sm:text-sm text-[#6c6456] mb-1 sm:mb-2">Местоположение *</label>
            <YandexAddress 
              v-model="newBook.location"
              placeholder="Начните вводить адрес для подсказок..."
              class="w-full px-3 sm:px-4 py-2 text-sm border border-[#7f8330]/30 rounded-lg focus:outline-none focus:border-[#7f8330]"
            />
          </div>
          
          <div class="mb-4 sm:mb-6">
            <label class="block text-xs sm:text-sm text-[#6c6456] mb-1 sm:mb-2">URL обложки (опционально)</label>
            <input 
              type="url" 
              v-model="newBook.image"
              class="w-full px-3 sm:px-4 py-2 text-sm border border-[#7f8330]/30 rounded-lg focus:outline-none focus:border-[#7f8330]"
            >
          </div>
          
          <div class="flex gap-2 sm:gap-3 justify-end">
            <button type="button" @click="closeModal" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm border border-[#7f8330] text-[#7f8330] rounded-lg">
              Отмена
            </button>
            <button type="submit" :disabled="submitting" class="btn-primary px-4 sm:px-6 py-1.5 sm:py-2 text-sm">
              {{ submitting ? 'Добавление...' : 'Добавить' }}
            </button>
          </div>
        </form>
      </div>
    </div>
    
    <!-- Модальное окно деталей книги -->
    <div v-if="showDetailsModal && selectedBook" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeDetailsModal">
      <div class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-md max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-3 sm:mb-4">
          <h3 class="text-lg sm:text-xl text-[#5e1104] font-['Playfair_Display'] line-clamp-2 flex-1 pr-2">{{ selectedBook.title }}</h3>
          <button @click="closeDetailsModal" class="text-[#6c6456] hover:text-[#5e1104] flex-shrink-0">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="text-center mb-3 sm:mb-4">
          <div class="h-24 sm:h-32 bg-[#f3d8ce] rounded-lg flex items-center justify-center mb-2 sm:mb-3">
            <img v-if="selectedBook.image" :src="selectedBook.image" :alt="selectedBook.title" class="h-20 sm:h-28 object-contain">
            <i v-else class="fas fa-book text-3xl sm:text-4xl text-[#7f8330]/30"></i>
          </div>
          <p class="text-xs sm:text-sm text-[#7f8330]">{{ selectedBook.author }}</p>
          <p v-if="selectedBook.genre" class="text-xs sm:text-sm text-[#7f8330] mt-1">
            Жанр: {{ selectedBook.genre }}
          </p>
        </div>
        
        <p v-if="selectedBook.description" class="text-xs sm:text-sm text-[#6c6456] mb-3 sm:mb-4">
          {{ selectedBook.description }}
        </p>
        
        <div class="text-xs sm:text-sm text-[#6c6456] mb-3 sm:mb-4 space-y-1">
          <p><strong>Статус:</strong> {{ getStatusText(selectedBook.status) }}</p>
          <p v-if="selectedBook.location"><strong>Где забрать:</strong> {{ selectedBook.location }}</p>
          <p v-if="selectedBook.owner"><strong>Добавил:</strong> {{ selectedBook.owner.name }}</p>
        </div>
        
        <div class="flex gap-2 sm:gap-3 justify-end">
          <button @click="closeDetailsModal" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm border border-[#7f8330] text-[#7f8330] rounded-lg">
            Закрыть
          </button>
          <button 
            v-if="selectedBook.status === 'available' && isAuthenticated && selectedBook.owner?.id !== currentUserId"
            @click="takeBook(selectedBook.id)"
            class="btn-primary px-4 sm:px-6 py-1.5 sm:py-2 text-sm"
          >
            Взять книгу
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { bookcrossingApi } from '@/api/bookcrossing'
import { useAuthStore } from '@/stores/auth'
import YandexAddress from '@/components/ui/YandexAddress.vue'

const authStore = useAuthStore()
const isAuthenticated = computed(() => authStore.isAuthenticated)
const currentUserId = computed(() => authStore.user?.id)

// Состояния
const loading = ref(false)
const books = ref([])
const myBooks = ref([])
const myTakenBooks = ref([])
const searchQuery = ref('')
const statusFilter = ref('')
const genreFilter = ref('')
const genresList = ref([])
const searchTimeout = ref(null)

// Пагинация
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

// Модальные окна
const showAddModal = ref(false)
const showDetailsModal = ref(false)
const selectedBook = ref(null)
const submitting = ref(false)

// Новая книга
const newBook = ref({
  title: '',
  author: '',
  genre: '',
  description: '',
  location: '',
  image: ''
})

// Популярные жанры для подсказок
const commonGenres = [
  'Роман',
  'Детектив',
  'Фантастика',
  'Фэнтези',
  'Поэзия',
  'Драма',
  'Комедия',
  'Трагедия',
  'Приключения',
  'Триллер',
  'Ужасы',
  'Биография',
  'Автобиография',
  'Научная литература',
  'Исторический роман',
  'Любовный роман',
  'Классика',
  'Современная проза',
  'Детская литература',
  'Психология'
]

const steps = [
  { title: 'Принесите книгу', description: 'Выберите книгу в хорошем состоянии и принесите в наш магазин' },
  { title: 'Оцените ассортимент', description: 'Посмотрите, какие книги доступны для обмена' },
  { title: 'Выберите книгу', description: 'Возьмите понравившуюся книгу бесплатно' },
]

// Объединённый список жанров (из API + популярные)
const allGenresList = computed(() => {
  const apiGenres = genresList.value || []
  const combined = [...new Set([...apiGenres, ...commonGenres])]
  return combined.sort()
})

// Загрузка списка жанров
const loadGenres = async () => {
  try {
    const response = await bookcrossingApi.getGenres()
    if (response.data.success) {
      genresList.value = response.data.data
    }
  } catch (error) {
    console.error('Ошибка загрузки жанров:', error)
    genresList.value = commonGenres
  }
}

// Загрузка книг
const loadBooks = async () => {
  loading.value = true
  
  try {
    const params = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page
    }
    
    if (searchQuery.value) params.search = searchQuery.value
    if (statusFilter.value) params.status = statusFilter.value
    if (genreFilter.value) params.genre = genreFilter.value
    
    const response = await bookcrossingApi.getBooks(params)
    
    if (response.data.success) {
      books.value = response.data.data.data
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total
      }
    }
  } catch (error) {
    console.error('Ошибка загрузки книг:', error)
  } finally {
    loading.value = false
  }
}

// Загрузка моих книг
const loadMyBooks = async () => {
  if (!isAuthenticated.value) return
  
  try {
    const [myBooksRes, myTakenRes] = await Promise.all([
      bookcrossingApi.getMyBooks(),
      bookcrossingApi.getMyTakenBooks()
    ])
    
    if (myBooksRes.data.success) {
      myBooks.value = myBooksRes.data.data
    }
    if (myTakenRes.data.success) {
      myTakenBooks.value = myTakenRes.data.data
    }
  } catch (error) {
    console.error('Ошибка загрузки моих книг:', error)
  }
}

// Добавление книги
const submitBook = async () => {
  if (!newBook.value.location) {
    alert('Пожалуйста, укажите местоположение книги')
    return
  }
  
  submitting.value = true
  
  try {
    const bookData = {
      title: newBook.value.title,
      author: newBook.value.author,
      description: newBook.value.description || null,
      genre: newBook.value.genre || null,
      location: newBook.value.location || null,
      image: newBook.value.image || null
    }
    
    const response = await bookcrossingApi.addBook(bookData)
    
    if (response.data.success) {
      alert('Книга успешно добавлена в буккроссинг!')
      closeModal()
      await loadBooks()
      await loadMyBooks()
      await loadGenres()
    }
  } catch (error) {
    console.error('Ошибка добавления книги:', error)
    alert(error.response?.data?.message || 'Не удалось добавить книгу. Попробуйте позже.')
  } finally {
    submitting.value = false
  }
}

// Взятие книги
const takeBook = async (bookId) => {
  if (!isAuthenticated.value) {
    alert('Войдите в систему, чтобы взять книгу')
    return
  }
  
  try {
    const response = await bookcrossingApi.takeBook(bookId)
    
    if (response.data.success) {
      alert(response.data.message)
      closeDetailsModal()
      await loadBooks()
      await loadMyBooks()
    }
  } catch (error) {
    console.error('Ошибка при взятии книги:', error)
    alert(error.response?.data?.message || 'Не удалось взять книгу')
  }
}

// Возврат книги
const returnBook = async (bookId) => {
  if (!confirm('Вы уверены, что хотите вернуть эту книгу?')) return
  
  try {
    const response = await bookcrossingApi.returnBook(bookId)
    
    if (response.data.success) {
      alert(response.data.message)
      await loadBooks()
      await loadMyBooks()
    }
  } catch (error) {
    console.error('Ошибка при возврате книги:', error)
    alert('Не удалось вернуть книгу')
  }
}

// Удаление книги
const deleteBook = async (bookId) => {
  if (!confirm('Вы уверены, что хотите удалить эту книгу из буккроссинга?')) return
  
  try {
    const response = await bookcrossingApi.deleteBook(bookId)
    
    if (response.data.success) {
      alert('Книга удалена')
      await loadBooks()
      await loadMyBooks()
      await loadGenres()
    }
  } catch (error) {
    console.error('Ошибка удаления книги:', error)
    alert('Не удалось удалить книгу')
  }
}

// Пагинация
const goToPage = (page) => {
  if (page < 1 || page > pagination.value.last_page) return
  pagination.value.current_page = page
  loadBooks()
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

// Поиск с debounce
const onSearchInput = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => {
    pagination.value.current_page = 1
    loadBooks()
  }, 500)
}

// Обработчик изменения жанра
const onGenreFilterChange = () => {
  pagination.value.current_page = 1
  loadBooks()
}

// Модальные окна
const openAddBookModal = () => {
  if (!isAuthenticated.value) {
    alert('Войдите в систему, чтобы добавить книгу')
    return
  }
  showAddModal.value = true
}

const closeModal = () => {
  showAddModal.value = false
  newBook.value = { title: '', author: '', genre: '', description: '', location: '', image: '' }
}

const showBookDetails = (book) => {
  selectedBook.value = book
  showDetailsModal.value = true
}

const closeDetailsModal = () => {
  showDetailsModal.value = false
  selectedBook.value = null
}

// Вспомогательные функции
const getStatusText = (status) => {
  const statusMap = {
    available: 'Доступна',
    taken: 'Занята',
    reserved: 'Зарезервирована'
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    available: 'bg-green-100 text-green-700',
    taken: 'bg-red-100 text-red-700',
    reserved: 'bg-yellow-100 text-yellow-700'
  }
  return classMap[status] || 'bg-gray-100 text-gray-700'
}

// Инициализация
onMounted(() => {
  loadGenres()
  loadBooks()
  if (isAuthenticated.value) {
    loadMyBooks()
  }
})

// Следим за авторизацией
watch(isAuthenticated, (newVal) => {
  if (newVal) {
    loadMyBooks()
  } else {
    myBooks.value = []
    myTakenBooks.value = []
  }
})
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.btn-primary {
  background: linear-gradient(135deg, #7f8330 0%, #b59b6d 100%);
  color: white;
  border-radius: 9999px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-title {
  display: none;
}
</style>