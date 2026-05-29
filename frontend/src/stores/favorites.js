import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'

export const useFavoritesStore = defineStore('favorites', () => {
  const items = ref([])
  const loading = ref(false)
  const error = ref(null)
  const initialized = ref(false)
  const favoriteBookIds = ref([]) // Изменено на массив

  const count = computed(() => favoriteBookIds.value.length)

  const loadFavorites = async () => {
    if (loading.value) return
    
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get('/favorites/ids')
      if (response.data.success) {
        favoriteBookIds.value = response.data.data
        initialized.value = true
      }
    } catch (err) {
      console.error('Ошибка загрузки избранного:', err)
      error.value = 'Не удалось загрузить избранное'
    } finally {
      loading.value = false
    }
  }

  const loadFavoritesFull = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get('/favorites')
      if (response.data.success) {
        items.value = response.data.data.data || []
        favoriteBookIds.value = items.value.map(book => book.id)
        initialized.value = true
      }
    } catch (err) {
      console.error('Ошибка загрузки избранного:', err)
      error.value = 'Не удалось загрузить избранное'
    } finally {
      loading.value = false
    }
  }

  const isBookFavorite = (bookId) => {
    return favoriteBookIds.value.includes(bookId)
  }

  const addToFavorites = async (bookId) => {
    if (favoriteBookIds.value.includes(bookId)) {
      return { success: false, message: 'Книга уже в избранном' }
    }
    
    try {
      const response = await api.post(`/favorites/${bookId}`)
      if (response.data.success) {
        // Обновляем favoriteBookIds из ответа сервера
        if (response.data.data?.favorite_ids) {
          favoriteBookIds.value = response.data.data.favorite_ids
        } else {
          favoriteBookIds.value.push(bookId)
        }
        return { success: true, message: response.data.message }
      }
    } catch (err) {
      const message = err.response?.data?.message || 'Не удалось добавить в избранное'
      return { success: false, message }
    }
  }

  const removeFromFavorites = async (bookId) => {
    if (!favoriteBookIds.value.includes(bookId)) {
      return { success: false, message: 'Книга не найдена в избранном' }
    }
    
    try {
      const response = await api.delete(`/favorites/${bookId}`)
      if (response.data.success) {
        // Обновляем favoriteBookIds из ответа сервера
        if (response.data.data?.favorite_ids) {
          favoriteBookIds.value = response.data.data.favorite_ids
        } else {
          favoriteBookIds.value = favoriteBookIds.value.filter(id => id !== bookId)
        }
        // Удаляем из списка, если он загружен
        items.value = items.value.filter(book => book.id !== bookId)
        return { success: true, message: response.data.message }
      }
    } catch (err) {
      const message = err.response?.data?.message || 'Не удалось удалить из избранного'
      return { success: false, message }
    }
  }

  return {
    items,
    loading,
    error,
    initialized,
    count,
    loadFavorites,
    loadFavoritesFull,
    isBookFavorite,
    addToFavorites,
    removeFromFavorites
  }
})