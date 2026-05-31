import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api/axios'

export const useCartStore = defineStore('cart', () => {
  const items = ref([])
  const loading = ref(false)
  const error = ref(null)
  const initialized = ref(false)
  const addingToCartMap = ref({})

  const cartItems = computed(() => items.value)
  
  // Только активные товары для расчета итогов
  const activeItems = computed(() => {
    return items.value.filter(item => item.is_active === true)
  })
  
  // Общая сумма ТОЛЬКО активных товаров
  const total = computed(() => {
    return activeItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
  })
  
  const itemsCount = computed(() => {
    return items.value.reduce((sum, item) => sum + item.quantity, 0)
  })
  
  // Количество только активных товаров
  const activeItemsCount = computed(() => {
    return activeItems.value.reduce((sum, item) => sum + item.quantity, 0)
  })

  const loadCart = async () => {
    if (loading.value) return
    
    loading.value = true
    error.value = null
    
    try {
      const response = await api.get('/cart')
      if (response.data.success) {
        const newItems = response.data.data.items.map(item => ({
          id: item.id,
          book_id: item.product_id, // product_id -> book_id для совместимости с фронтом
          title: item.product?.title || 'Товар',
          price: item.price,
          quantity: item.quantity,
          image: item.product?.cover_image || '/images/default-product.jpg',
          is_in_stock: item.product?.is_in_stock || true,
          is_active: item.product?.is_active || false,
          quantity_in_stock: item.product?.quantity || 0,
          color: item.color,
          size: item.size,
          color_label: item.color_label,
          size_label: item.size_label
        }))
        items.value = newItems
        initialized.value = true
      }
    } catch (err) {
      console.error('Ошибка загрузки корзины:', err)
      error.value = 'Не удалось загрузить корзину'
    } finally {
      loading.value = false
    }
  }

  const addToCart = async (book, quantity = 1) => {
    const bookId = book.id
    const color = book.color || null
    const size = book.size || null
    const itemKey = `${bookId}_${color || 'none'}_${size || 'none'}`
    
    // Проверка: если уже добавляем ЭТУ книгу с такими же опциями, игнорируем
    if (addingToCartMap.value[itemKey]) {
      return { success: false, message: 'Добавление уже выполняется...' }
    }
    
    addingToCartMap.value[itemKey] = true
    loading.value = true
    error.value = null
    
    try {
      // Отправляем product_id (как ожидает бэкенд) вместо book_id
      const response = await api.post('/cart/add', {
        product_id: book.id,  // key: product_id, value: book.id
        quantity: quantity,
        color: color,
        size: size
      })
      
      if (response.data.success) {
        let newItems = null
        
        if (response.data.data && response.data.data.items) {
          newItems = response.data.data.items
        } else if (response.data.items) {
          newItems = response.data.items
        }
        
        if (newItems) {
          items.value = newItems.map(item => ({
            id: item.id,
            book_id: item.product_id,
            title: item.product?.title || 'Товар',
            price: item.price,
            quantity: item.quantity,
            image: item.product?.cover_image || '/images/default-product.jpg',
            is_in_stock: item.product?.is_in_stock || true,
            is_active: item.product?.is_active || false,
            quantity_in_stock: item.product?.quantity || 0,
            color: item.color,
            size: item.size,
            color_label: item.color_label,
            size_label: item.size_label
          }))
        } else {
          await loadCart()
        }
        
        return { success: true, message: response.data.message }
      }
    } catch (err) {
      console.error('Ошибка добавления в корзину:', err.response?.data)
      const message = err.response?.data?.message || 'Не удалось добавить товар в корзину'
      error.value = message
      return { success: false, message }
    } finally {
      loading.value = false
      delete addingToCartMap.value[itemKey]
    }
  }

  const updateQuantity = async (itemId, newQuantity) => {
    if (newQuantity < 1) {
      return removeItem(itemId)
    }
    
    loading.value = true
    
    try {
      const response = await api.put(`/cart/items/${itemId}`, {
        quantity: newQuantity
      })
      
      if (response.data.success) {
        const item = items.value.find(i => i.id === itemId)
        if (item) {
          item.quantity = newQuantity
        }
        return { success: true }
      }
    } catch (err) {
      const message = err.response?.data?.message || 'Не удалось обновить количество'
      error.value = message
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const removeItem = async (itemId) => {
    loading.value = true
    
    try {
      const response = await api.delete(`/cart/items/${itemId}`)
      
      if (response.data.success) {
        items.value = items.value.filter(item => item.id !== itemId)
        return { success: true }
      }
    } catch (err) {
      error.value = 'Не удалось удалить товар'
      return { success: false }
    } finally {
      loading.value = false
    }
  }

  const clearCart = async () => {
    loading.value = true
    
    try {
      const response = await api.delete('/cart/clear')
      
      if (response.data.success) {
        items.value = []
        return { success: true }
      }
    } catch (err) {
      error.value = 'Не удалось очистить корзину'
      return { success: false }
    } finally {
      loading.value = false
    }
  }

  const mergeCart = async () => {
    try {
      const response = await api.post('/cart/merge')
      if (response.data.success) {
        await loadCart()
      }
    } catch (err) {
      console.error('Ошибка объединения корзины:', err)
    }
  }

  return {
    items,
    loading,
    error,
    initialized,
    cartItems,
    activeItems,
    total,
    itemsCount,
    activeItemsCount,
    loadCart,
    addToCart,
    updateQuantity,
    removeItem,
    clearCart,
    mergeCart
  }
})