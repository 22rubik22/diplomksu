<template>
  <div>
    <h1 class="page-title">Авторские встречи</h1>
    
    <div class="bg-white rounded-xl p-4 sm:p-6 md:p-8 shadow-[0_5px_25px_rgba(0,0,0,0.05)] border border-[#f3d8ce]/50">
      <!-- Фильтры - горизонтальный скролл на мобильных -->
      <div class="mb-6 sm:mb-8 pb-4 border-b border-[#f3d8ce] overflow-x-auto whitespace-nowrap scrollbar-hide">
        <div class="flex gap-2 sm:gap-3">
          <button 
            v-for="filter in filters" 
            :key="filter.value"
            @click="currentFilter = filter.value"
            class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-full transition-all text-sm sm:text-base"
            :class="currentFilter === filter.value ? 'bg-[#7f8330] text-white' : 'text-[#6c6456] hover:bg-[#7f8330]/10'"
          >
            {{ filter.label }}
          </button>
        </div>
      </div>
      
      <!-- Предстоящие встречи -->
      <div v-if="upcomingEvents.length" class="mb-8 sm:mb-12">
        <h3 class="text-[#5e1104] text-xl sm:text-2xl mb-4 sm:mb-6">Предстоящие встречи</h3>
        
        <div class="space-y-3 sm:space-y-4">
          <div 
            v-for="event in upcomingEvents" 
            :key="event.id"
            class="flex flex-col p-4 sm:p-6 border border-[#7f8330]/10 rounded-xl hover:shadow-md transition-shadow"
          >
            <!-- Изображение и тип события -->
            <div class="flex gap-4 sm:gap-6 mb-4">
              <div class="w-20 h-20 sm:w-28 sm:h-28 md:w-32 md:h-32 bg-[#f3d8ce] rounded-xl flex items-center justify-center overflow-hidden flex-shrink-0">
                <img v-if="event.image" :src="event.image" :alt="event.title" class="w-full h-full object-cover">
                <i v-else class="fas fa-calendar-alt text-2xl sm:text-4xl text-[#7f8330]"></i>
              </div>
              
              <div class="flex-1">
                <span class="inline-block bg-[#7f8330]/10 px-2 py-0.5 sm:py-1 rounded text-[10px] sm:text-sm mb-2">
                  {{ getEventTypeText(event.event_type) }}
                </span>
                <h4 class="text-[#5e1104] text-base sm:text-lg md:text-xl font-['Playfair_Display'] line-clamp-2">{{ event.title }}</h4>
                <p class="text-xs sm:text-sm text-[#7f8330] mt-1">
                  с {{ event.author?.name || 'Приглашённый гость' }}
                </p>
              </div>
            </div>
            
            <!-- Информация о мероприятии -->
            <div class="grid grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm text-[#6c6456] mb-3 sm:mb-4">
              <span><i class="far fa-calendar-alt mr-1 sm:mr-2 w-3 sm:w-4"></i>{{ formatDate(event.start_date) }}</span>
              <span><i class="far fa-clock mr-1 sm:mr-2 w-3 sm:w-4"></i>{{ formatTime(event.start_date) }}{{ event.end_date ? ` - ${formatTime(event.end_date)}` : '' }}</span>
              <span><i class="fas fa-map-marker-alt mr-1 sm:mr-2 w-3 sm:w-4"></i>{{ event.location }}</span>
              <span>
                <i class="fas fa-tag mr-1 sm:mr-2 w-3 sm:w-4"></i>
                {{ event.price > 0 ? formatPrice(event.price) + ' ₽' : 'Бесплатно' }}
              </span>
            </div>
            
            <!-- Описание -->
            <p class="text-xs sm:text-sm text-[#6c6456] mb-3 sm:mb-4 line-clamp-2">{{ event.description }}</p>
            
            <!-- Кнопки и количество мест -->
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
              <div class="flex gap-2 sm:gap-3">
                <button 
                  @click="showEventDetails(event)" 
                  class="btn px-3 sm:px-4 py-1.5 sm:py-2 text-sm"
                >
                  Подробнее
                </button>
                <button 
                  v-if="!isRegistered(event.id)"
                  @click="registerForEvent(event.id)" 
                  class="btn-primary px-3 sm:px-4 py-1.5 sm:py-2 text-sm whitespace-nowrap"
                  :disabled="event.is_full || !isAuthenticated"
                >
                  {{ event.is_full ? 'Мест нет' : 'Записаться' }}
                </button>
                <button 
                  v-else
                  @click="cancelRegistration(event.id)" 
                  class="btn-secondary px-3 sm:px-4 py-1.5 sm:py-2 text-sm"
                >
                  Отменить
                </button>
              </div>
              
              <div class="inline-flex items-center justify-center sm:justify-end">
                <span class="bg-[#7f8330] text-white px-2 sm:px-3 py-1 rounded-full text-[10px] sm:text-xs whitespace-nowrap">
                  {{ event.available_seats || 0 }} мест
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Состояние загрузки предстоящих -->
      <div v-else-if="loadingUpcoming" class="text-center py-8 sm:py-12">
        <i class="fas fa-spinner fa-spin text-2xl sm:text-3xl text-[#7f8330]"></i>
        <p class="text-sm sm:text-base text-[#6c6456] mt-2">Загрузка мероприятий...</p>
      </div>
      
      <!-- Прошедшие встречи -->
      <div v-if="pastEvents.length">
        <h3 class="text-[#5e1104] text-xl sm:text-2xl mb-4 sm:mb-6">Прошедшие встречи</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
          <div 
            v-for="event in pastEvents" 
            :key="event.id"
            class="flex gap-3 sm:gap-4 p-3 sm:p-4 border border-[#7f8330]/10 rounded-lg opacity-75 hover:opacity-100 transition-opacity"
          >
            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-[#f3d8ce] rounded-lg flex items-center justify-center overflow-hidden flex-shrink-0">
              <img v-if="event.image" :src="event.image" :alt="event.title" class="w-full h-full object-cover">
              <i v-else class="fas fa-calendar-alt text-xl sm:text-2xl text-[#7f8330]"></i>
            </div>
            
            <div class="flex-1 min-w-0">
              <h4 class="text-[#5e1104] font-medium text-sm sm:text-base line-clamp-2">{{ event.title }}</h4>
              <p class="text-xs sm:text-sm text-[#7f8330] line-clamp-1">{{ event.author?.name || 'Приглашённый гость' }}</p>
              <p class="text-[10px] sm:text-xs text-[#6c6456] mt-1">{{ formatDate(event.start_date) }}</p>
              <button 
                @click="showEventDetails(event)" 
                class="text-[#7f8330] text-[10px] sm:text-sm hover:underline mt-1 sm:mt-2 inline-block"
              >
                Подробнее →
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Состояние загрузки прошедших -->
      <div v-else-if="loadingPast" class="text-center py-8 sm:py-12">
        <i class="fas fa-spinner fa-spin text-2xl sm:text-3xl text-[#7f8330]"></i>
        <p class="text-sm sm:text-base text-[#6c6456] mt-2">Загрузка прошедших мероприятий...</p>
      </div>
      
      <!-- Пустое состояние -->
      <div v-if="!loadingUpcoming && !loadingPast && upcomingEvents.length === 0 && pastEvents.length === 0" class="text-center py-12 sm:py-16">
        <i class="fas fa-calendar-times text-4xl sm:text-5xl text-[#7f8330]/30 mb-3 sm:mb-4"></i>
        <p class="text-base sm:text-lg text-[#6c6456]">Мероприятия не найдены</p>
      </div>
      
    </div>
    
    <!-- Модальное окно деталей мероприятия -->
    <div v-if="showModal && selectedEvent" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeModal">
      <div class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start mb-3 sm:mb-4">
          <h3 class="text-lg sm:text-xl text-[#5e1104] font-['Playfair_Display'] line-clamp-2 flex-1 pr-2">{{ selectedEvent.title }}</h3>
          <button @click="closeModal" class="text-[#6c6456] hover:text-[#5e1104] flex-shrink-0">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3 text-xs sm:text-sm text-[#6c6456] mb-3 sm:mb-4">
          <span><i class="far fa-calendar-alt mr-2"></i>{{ formatDate(selectedEvent.start_date) }}</span>
          <span><i class="far fa-clock mr-2"></i>{{ formatTime(selectedEvent.start_date) }}</span>
          <span><i class="fas fa-map-marker-alt mr-2"></i>{{ selectedEvent.location }}</span>
          <span><i class="fas fa-tag mr-2"></i>{{ selectedEvent.price > 0 ? formatPrice(selectedEvent.price) + ' ₽' : 'Бесплатно' }}</span>
        </div>
        
        <div class="bg-[#f8f6f3] rounded-lg p-3 sm:p-4 mb-3 sm:mb-4">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div>
              <span class="text-xs sm:text-sm text-[#6c6456]">Цена:</span>
              <span class="text-base sm:text-xl text-[#5e1104] font-semibold ml-2">
                {{ selectedEvent.price > 0 ? formatPrice(selectedEvent.price) + ' ₽' : 'Бесплатно' }}
              </span>
            </div>
            <div>
              <span class="text-xs sm:text-sm text-[#6c6456]">Свободных мест:</span>
              <span class="text-base sm:text-xl font-semibold ml-2" :class="selectedEvent.available_seats > 0 ? 'text-green-600' : 'text-red-600'">
                {{ selectedEvent.available_seats || 0 }}
              </span>
            </div>
          </div>
        </div>
        
        <div class="mb-3 sm:mb-4">
          <h4 class="text-[#5e1104] font-medium text-sm sm:text-base mb-1 sm:mb-2">Описание</h4>
          <p class="text-xs sm:text-sm text-[#6c6456] whitespace-pre-line">{{ selectedEvent.description }}</p>
        </div>
        
        <div v-if="selectedEvent.author" class="mb-3 sm:mb-4">
          <h4 class="text-[#5e1104] font-medium text-sm sm:text-base mb-1 sm:mb-2">Об авторе</h4>
          <p class="text-xs sm:text-sm text-[#6c6456]">{{ selectedEvent.author.bio || 'Информация об авторе скоро появится' }}</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 justify-end mt-4 sm:mt-6">
          <button @click="closeModal" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm border border-[#7f8330] text-[#7f8330] rounded-lg order-2 sm:order-1">
            Закрыть
          </button>
          <button 
            v-if="selectedEvent.start_date > new Date().toISOString()"
            @click="handleRegistrationAction(selectedEvent)"
            class="btn-primary px-4 sm:px-6 py-1.5 sm:py-2 text-sm order-1 sm:order-2"
            :disabled="selectedEvent.is_full || !isAuthenticated"
          >
            {{ isRegistered(selectedEvent.id) ? 'Отменить запись' : (selectedEvent.is_full ? 'Мест нет' : 'Записаться') }}
          </button>
        </div>
      </div>
    </div>
    
    <!-- Модальное окно регистрации -->
    <div v-if="showRegisterModal && registeringEvent" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="closeRegisterModal">
      <div class="bg-white rounded-xl p-4 sm:p-6 w-full max-w-md">
        <h3 class="text-base sm:text-xl text-[#5e1104] font-['Playfair_Display'] mb-3 sm:mb-4">Запись на мероприятие</h3>
        <p class="text-xs sm:text-sm text-[#6c6456] mb-3 sm:mb-4 line-clamp-2">{{ registeringEvent.title }}</p>
        
        <form @submit.prevent="submitRegistration">
          <div class="mb-3 sm:mb-4">
            <label class="block text-xs sm:text-sm text-[#6c6456] mb-1 sm:mb-2">Количество мест *</label>
            <input 
              type="number" 
              v-model.number="registrationData.attendees_count"
              min="1"
              :max="registeringEvent.available_seats || 10"
              required
              class="w-full px-3 sm:px-4 py-1.5 sm:py-2 text-sm border border-[#7f8330]/30 rounded-lg focus:outline-none focus:border-[#7f8330]"
            >
            <p class="text-[10px] sm:text-xs text-[#6c6456] mt-1">Доступно мест: {{ registeringEvent.available_seats || 0 }}</p>
          </div>
          
          <div class="mb-4 sm:mb-6">
            <label class="block text-xs sm:text-sm text-[#6c6456] mb-1 sm:mb-2">Комментарий (опционально)</label>
            <textarea 
              v-model="registrationData.comment"
              rows="3"
              class="w-full px-3 sm:px-4 py-1.5 sm:py-2 text-sm border border-[#7f8330]/30 rounded-lg focus:outline-none focus:border-[#7f8330]"
              placeholder="Ваши вопросы или пожелания"
            ></textarea>
          </div>
          
          <div class="flex gap-2 sm:gap-3 justify-end">
            <button type="button" @click="closeRegisterModal" class="px-3 sm:px-4 py-1.5 sm:py-2 text-sm border border-[#7f8330] text-[#7f8330] rounded-lg">
              Отмена
            </button>
            <button type="submit" :disabled="submitting" class="btn-primary px-4 sm:px-6 py-1.5 sm:py-2 text-sm">
              {{ submitting ? 'Отправка...' : 'Записаться' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { eventApi } from '@/api/events'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()
const isAuthenticated = computed(() => authStore.isAuthenticated)

// Фильтры
const filters = [
  { value: 'all', label: 'Все' },
  { value: 'author_meeting', label: 'Встречи с авторами' },
  { value: 'presentation', label: 'Презентации' },
  { value: 'lecture', label: 'Лекции' },
  { value: 'workshop', label: 'Мастер-классы' }
]

// Состояния
const loadingUpcoming = ref(false)
const loadingPast = ref(false)
const upcomingEvents = ref([])
const pastEvents = ref([])
const currentFilter = ref('all')
const registeredEvents = ref(new Set())

// Модальные окна
const showModal = ref(false)
const showRegisterModal = ref(false)
const selectedEvent = ref(null)
const registeringEvent = ref(null)
const submitting = ref(false)

// Данные регистрации
const registrationData = ref({
  attendees_count: 1,
  comment: ''
})

// Загрузка моих регистраций
const loadMyRegistrations = async () => {
  if (!isAuthenticated.value) return
  
  try {
    const response = await eventApi.getMyRegistrations()
    if (response.data.success) {
      registeredEvents.value.clear()
      response.data.data.forEach(reg => {
        registeredEvents.value.add(reg.event.id)
      })
    }
  } catch (error) {
    console.error('Ошибка загрузки регистраций:', error)
  }
}

// Проверка, зарегистрирован ли пользователь
const isRegistered = (eventId) => {
  return registeredEvents.value.has(eventId)
}

// Загрузка мероприятий
const loadEvents = async () => {
  await Promise.all([loadUpcomingEvents(), loadPastEvents()])
}

const loadUpcomingEvents = async () => {
  loadingUpcoming.value = true
  
  try {
    const params = {
      upcoming: true,
      per_page: 20
    }
    
    if (currentFilter.value !== 'all') {
      params.event_type = currentFilter.value
    }
    
    const response = await eventApi.getEvents(params)
    
    if (response.data.success) {
      upcomingEvents.value = response.data.data.data
    }
  } catch (error) {
    console.error('Ошибка загрузки предстоящих мероприятий:', error)
  } finally {
    loadingUpcoming.value = false
  }
}

const loadPastEvents = async () => {
  loadingPast.value = true
  
  try {
    const params = {
      upcoming: false,
      per_page: 20
    }
    
    if (currentFilter.value !== 'all') {
      params.event_type = currentFilter.value
    }
    
    const response = await eventApi.getEvents(params)
    
    if (response.data.success) {
      pastEvents.value = response.data.data.data
    }
  } catch (error) {
    console.error('Ошибка загрузки прошедших мероприятий:', error)
  } finally {
    loadingPast.value = false
  }
}

// Регистрация на мероприятие
const registerForEvent = async (eventId) => {
  if (!isAuthenticated.value) {
    alert('Войдите в систему, чтобы записаться на мероприятие')
    return
  }
  
  const event = upcomingEvents.value.find(e => e.id === eventId)
  if (!event) return
  
  registeringEvent.value = event
  registrationData.value = { attendees_count: 1, comment: '' }
  showRegisterModal.value = true
}

const submitRegistration = async () => {
  submitting.value = true
  
  try {
    const response = await eventApi.registerForEvent(registeringEvent.value.id, registrationData.value)
    
    if (response.data.success) {
      alert('Вы успешно зарегистрированы на мероприятие!')
      closeRegisterModal()
      await loadUpcomingEvents()
      await loadMyRegistrations()
    }
  } catch (error) {
    console.error('Ошибка регистрации:', error)
    alert(error.response?.data?.message || 'Не удалось зарегистрироваться')
  } finally {
    submitting.value = false
  }
}

// Отмена регистрации
const cancelRegistration = async (eventId) => {
  if (!confirm('Вы уверены, что хотите отменить запись?')) return
  
  try {
    const response = await eventApi.cancelRegistration(eventId)
    
    if (response.data.success) {
      alert('Регистрация отменена')
      await loadUpcomingEvents()
      await loadMyRegistrations()
    }
  } catch (error) {
    console.error('Ошибка отмены регистрации:', error)
    alert('Не удалось отменить регистрацию')
  }
}

// Модальные окна
const showEventDetails = (event) => {
  selectedEvent.value = event
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedEvent.value = null
}

const closeRegisterModal = () => {
  showRegisterModal.value = false
  registeringEvent.value = null
}

const handleRegistrationAction = (event) => {
  if (isRegistered(event.id)) {
    cancelRegistration(event.id)
  } else {
    registerForEvent(event.id)
  }
}

// Предложение автора
const suggestAuthor = () => {
  alert('Форма для предложения автора появится в ближайшее время. Пока вы можете написать нам на ivy@books.ru')
}

// Вспомогательные функции
const getEventTypeText = (type) => {
  const types = {
    author_meeting: 'Встреча с автором',
    presentation: 'Презентация',
    lecture: 'Лекция',
    workshop: 'Мастер-класс'
  }
  return types[type] || type
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const formatTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleTimeString('ru-RU', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('ru-RU').format(price)
}

// Следим за изменением фильтра
watch(currentFilter, () => {
  loadUpcomingEvents()
  loadPastEvents()
})

// Инициализация
onMounted(() => {
  loadEvents()
  if (isAuthenticated.value) {
    loadMyRegistrations()
  }
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Скрываем скроллбар для горизонтального скролла на мобильных */
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
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

.btn {
  border: 1px solid #7f8330;
  color: #7f8330;
  border-radius: 9999px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn:hover {
  background: rgba(127, 131, 48, 0.08);
  transform: translateY(-1px);
}

.btn-secondary {
  background: #6c6456;
  color: white;
  border-radius: 9999px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: #5e1104;
  transform: translateY(-1px);
}

.page-title {
  display: none;
}
</style>