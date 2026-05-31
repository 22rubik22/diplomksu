<template>
  <div>
    <!-- Заголовок секции -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
          <i class="fas fa-star text-[#c8a87c] text-lg"></i>
        </div>
        <div>
          <h3 class="text-xl font-light text-[#2c2c2c]">Управление отзывами</h3>
          <p class="text-xs text-[#8b7355] mt-0.5">Модерация и управление отзывами покупателей</p>
        </div>
      </div>
    </div>

    <!-- Фильтры -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="relative">
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-[#8b7355] text-sm"></i>
        <input 
          v-model="filters.search"
          type="text"
          placeholder="Поиск по товару или пользователю..."
          class="w-full pl-10 pr-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all"
          @input="searchReviews"
        >
      </div>
      
      <select v-model="filters.status" class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer" @change="loadReviews">
        <option :value="null">Все отзывы</option>
        <option value="pending">На модерации</option>
        <option value="approved">Одобренные</option>
      </select>
      
      <select v-model="filters.rating" class="px-4 py-2.5 text-sm border border-[#e8e0d8] rounded-xl bg-white focus:outline-none focus:border-[#c8a87c] transition-all cursor-pointer" @change="loadReviews">
        <option :value="null">Все оценки</option>
        <option value="5">5 ★</option>
        <option value="4">4 ★</option>
        <option value="3">3 ★</option>
        <option value="2">2 ★</option>
        <option value="1">1 ★</option>
      </select>
    </div>

    <!-- Таблица отзывов -->
    <div class="overflow-x-auto bg-white rounded-2xl shadow-sm border border-[#e8e0d8]">
      <table class="w-full border-collapse">
        <thead>
          <tr class="border-b border-[#e8e0d8] bg-[#faf8f5]">
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Товар</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Пользователь</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Оценка</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Отзыв</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Статус</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Дата</th>
            <th class="text-left p-4 text-[#8b7355] font-medium text-xs uppercase tracking-wider">Действия</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading && reviews.length === 0">
            <td colspan="8" class="p-8 text-center">
              <div class="w-6 h-6 border-2 border-[#c8a87c] border-t-transparent rounded-full animate-spin mx-auto"></div>
              <p class="mt-3 text-[#8b7355] text-sm">Загрузка...</p>
            </td>
          </tr>
          <tr v-else-if="reviews.length === 0">
            <td colspan="8" class="p-8 text-center">
              <i class="fas fa-star text-3xl text-[#e8e0d8] mb-2"></i>
              <p class="text-[#8b7355] text-sm">Отзывы не найдены</p>
            </td>
          </tr>
          <tr v-for="review in reviews" :key="review.id" class="border-b border-[#e8e0d8] hover:bg-[#faf8f5] transition-colors">
            <td class="p-4">
              <div class="text-[#2c2c2c] text-sm font-medium truncate max-w-[180px]" :title="review.book?.title">
                {{ review.product?.title || '—' }}
              </div>
            </td>
            <td class="p-4">
              <div class="text-[#2c2c2c] text-sm font-medium truncate max-w-[150px]" :title="review.user?.name">
                {{ review.user?.name || '—' }}
              </div>
              <div class="text-[10px] text-[#8b7355] truncate">{{ review.user?.email || '—' }}</div>
            </td>
            <td class="p-4">
              <div class="flex items-center gap-1">
                <i class="fas fa-star text-[#c8a87c] text-xs"></i>
                <span class="text-[#2c2c2c] text-sm font-medium">{{ review.rating }}</span>
              </div>
            </td>
            <td class="p-4">
              <div class="text-[#2c2c2c] text-sm font-medium truncate max-w-[200px]" :title="review.title">
                {{ review.title || 'Без заголовка' }}
              </div>
              <div class="text-[10px] text-[#8b7355] truncate max-w-[200px]" :title="review.comment">
                {{ review.comment || '—' }}
              </div>
            </td>
            <td class="p-4">
              <span 
                class="px-2 py-1 rounded-full text-[10px] font-medium"
                :class="review.is_approved ? 'bg-green-50 text-green-600' : 'bg-yellow-50 text-yellow-600'"
              >
                <i :class="review.is_approved ? 'fas fa-check-circle' : 'fas fa-clock'" class="text-[9px] mr-1"></i>
                {{ review.is_approved ? 'Одобрен' : 'На модерации' }}
              </span>
            </td>
            <td class="p-4 text-[#8b7355] text-sm">{{ formatDate(review.created_at) }}</td>
            <td class="p-4">
              <div class="flex gap-2">
                <button 
                  @click="openModal(review)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-[#8b7355] hover:bg-[#faf8f5] hover:text-[#c8a87c] transition-all"
                  title="Просмотр"
                >
                  <i class="fas fa-eye text-xs"></i>
                </button>
                <button 
                  v-if="!review.is_approved"
                  @click="approveReview(review)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-green-500 hover:bg-green-50 hover:text-green-600 transition-all"
                  title="Одобрить"
                >
                  <i class="fas fa-check text-xs"></i>
                </button>
                <button 
                  @click="rejectReview(review)" 
                  class="w-8 h-8 rounded-lg border border-[#e8e0d8] text-red-400 hover:bg-red-50 hover:text-red-500 transition-all"
                  title="Удалить"
                >
                  <i class="fas fa-trash-alt text-xs"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      
      <!-- Пагинация -->
      <div v-if="pagination.last_page > 1" class="flex justify-between items-center px-6 py-4 border-t border-[#e8e0d8]">
        <div class="text-xs text-[#8b7355]">
          {{ reviews.length }} из {{ pagination.total }}
        </div>
        <div class="flex gap-2">
          <button 
            @click="loadReviews(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="w-8 h-8 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <i class="fas fa-chevron-left text-xs"></i>
          </button>
          <span class="px-3 py-1.5 rounded-xl bg-[#c8a87c] text-white text-sm">
            {{ pagination.current_page }}
          </span>
          <button 
            @click="loadReviews(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="w-8 h-8 rounded-xl border border-[#e8e0d8] bg-white hover:bg-[#faf8f5] transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
          >
            <i class="fas fa-chevron-right text-xs"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Модальное окно деталей отзыва -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-2xl w-full max-w-2xl shadow-xl transform transition-all animate-modal-slide max-h-[90vh] overflow-y-auto">
        <!-- Заголовок -->
        <div class="border-b border-[#e8e0d8] px-6 py-4 sticky top-0 bg-white z-10">
          <div class="flex justify-between items-center">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-[#c8a87c]/10 flex items-center justify-center">
                <i class="fas fa-star text-[#c8a87c] text-lg"></i>
              </div>
              <div>
                <h3 class="text-xl font-light text-[#2c2c2c]">Детали отзыва</h3>
                <p class="text-xs text-[#8b7355] mt-0.5">ID: {{ selectedReview?.id }}</p>
              </div>
            </div>
            <button @click="closeModal" class="text-[#8b7355] hover:text-[#2c2c2c] transition-colors">
              <i class="fas fa-times text-xl"></i>
            </button>
          </div>
        </div>
        
        <div class="p-6" v-if="selectedReview">
          <!-- Информация о товаре -->
          <div class="p-4 rounded-xl bg-[#faf8f5] mb-5">
            <h4 class="text-sm font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
              <i class="fas fa-shopping-bag text-[#c8a87c] text-xs"></i>
              Товар
            </h4>
            <div class="space-y-2">
              <p><span class="text-[#8b7355] text-xs">Название:</span> <span class="text-[#2c2c2c] text-sm ml-2">{{ selectedReview.book?.title || '—' }}</span></p>
              <p><span class="text-[#8b7355] text-xs">Бренд:</span> <span class="text-[#2c2c2c] text-sm ml-2">{{ selectedReview.book?.author?.name || '—' }}</span></p>
            </div>
          </div>
          
          <!-- Информация о пользователе -->
          <div class="p-4 rounded-xl bg-[#faf8f5] mb-5">
            <h4 class="text-sm font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
              <i class="fas fa-user text-[#c8a87c] text-xs"></i>
              Пользователь
            </h4>
            <div class="space-y-2">
              <p><span class="text-[#8b7355] text-xs">Имя:</span> <span class="text-[#2c2c2c] text-sm ml-2">{{ selectedReview.user?.name || '—' }}</span></p>
              <p><span class="text-[#8b7355] text-xs">Email:</span> <span class="text-[#2c2c2c] text-sm ml-2">{{ selectedReview.user?.email || '—' }}</span></p>
            </div>
          </div>
          
          <!-- Отзыв -->
          <div class="p-4 rounded-xl bg-[#faf8f5] mb-5">
            <h4 class="text-sm font-medium text-[#2c2c2c] mb-3 flex items-center gap-2">
              <i class="fas fa-comment text-[#c8a87c] text-xs"></i>
              Отзыв
            </h4>
            <div class="space-y-3">
              <div class="flex items-center gap-2">
                <span class="text-[#8b7355] text-xs">Оценка:</span>
                <div class="flex items-center gap-1">
                  <i class="fas fa-star text-[#c8a87c] text-sm"></i>
                  <span class="text-[#2c2c2c] text-sm font-medium">{{ selectedReview.rating }}</span>
                </div>
              </div>
              <div>
                <span class="text-[#8b7355] text-xs">Заголовок:</span>
                <p class="mt-1 text-[#2c2c2c] text-sm font-medium">{{ selectedReview.title || 'Без заголовка' }}</p>
              </div>
              <div>
                <span class="text-[#8b7355] text-xs">Комментарий:</span>
                <p class="mt-1 text-[#8b7355] text-sm leading-relaxed">{{ selectedReview.comment || '—' }}</p>
              </div>
              <div class="pt-2 border-t border-[#e8e0d8]">
                <p class="text-[10px] text-[#8b7355]">Создан: {{ formatDate(selectedReview.created_at) }}</p>
              </div>
            </div>
          </div>
          
          <!-- Кнопки действий -->
          <div class="flex gap-3 pt-2">
            <button 
              v-if="!selectedReview.is_approved"
              @click="approveReview(selectedReview); closeModal()" 
              class="flex-1 px-4 py-2.5 rounded-xl bg-[#c8a87c] text-white text-sm hover:bg-[#b89a6e] transition-all flex items-center justify-center gap-2"
            >
              <i class="fas fa-check text-xs"></i>
              Одобрить
            </button>
            <button 
              @click="rejectReview(selectedReview); closeModal()" 
              class="flex-1 px-4 py-2.5 rounded-xl border border-red-400 text-red-500 text-sm hover:bg-red-50 transition-all flex items-center justify-center gap-2"
            >
              <i class="fas fa-trash-alt text-xs"></i>
              Удалить
            </button>
            <button 
              @click="closeModal" 
              class="px-6 py-2.5 rounded-xl border border-[#e8e0d8] text-[#8b7355] text-sm hover:bg-[#faf8f5] transition-all"
            >
              Закрыть
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { reviewApi } from '@/api/reviews'
import { useToast } from '@/composables/useToast'

const { success, error } = useToast()

const reviews = ref([])
const loading = ref(false)
const showModal = ref(false)
const selectedReview = ref(null)
const searchTimeout = ref(null)

const filters = ref({
  search: '',
  status: null,
  rating: null
})

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 20,
  total: 0
})

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  })
}

const loadReviews = async (page = 1) => {
  loading.value = true
  try {
    const params = { page, per_page: 20 }
    if (filters.value.status === 'pending') params.status = 'pending'
    else if (filters.value.status === 'approved') params.status = 'approved'
    
    const response = await reviewApi.getAllReviews(params)
    if (response.data.success) {
      let data = response.data.data.data
      
      if (filters.value.rating) {
        data = data.filter(r => r.rating === parseInt(filters.value.rating))
      }
      
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        data = data.filter(r => 
          r.book?.title?.toLowerCase().includes(search) ||
          r.user?.name?.toLowerCase().includes(search) ||
          r.user?.email?.toLowerCase().includes(search)
        )
      }
      
      reviews.value = data
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total
      }
    }
  } catch (err) {
    console.error(err)
    error('Ошибка при загрузке отзывов')
  } finally {
    loading.value = false
  }
}

const searchReviews = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value)
  searchTimeout.value = setTimeout(() => loadReviews(), 500)
}

const approveReview = async (review) => {
  try {
    const response = await reviewApi.approveReview(review.id)
    if (response.data.success) {
      review.is_approved = true
      success('Отзыв одобрен')
    }
  } catch (err) {
    error('Ошибка при одобрении')
  }
}

const rejectReview = async (review) => {
  if (confirm(`Удалить отзыв?`)) {
    try {
      const response = await reviewApi.rejectReview(review.id)
      if (response.data.success) {
        success('Отзыв удалён')
        await loadReviews()
      }
    } catch (err) {
      error('Ошибка при удалении')
    }
  }
}

const openModal = (review) => {
  selectedReview.value = review
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedReview.value = null
}

onMounted(() => loadReviews())
</script>

<style scoped>
.animate-modal-slide {
  animation: modalSlide 0.2s ease-out;
}
@keyframes modalSlide {
  from { opacity: 0; transform: scale(0.95) translateY(-10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>