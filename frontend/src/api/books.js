import api from './axios'

export const bookApi = {
  // Получить список книг
  getBooks(params = {}) {
    return api.get('/products', { params })
  },
  
  // Получить книгу по ID
  getBook(id) {
    return api.get(`/products/${id}`)
  },
  
  // Получить книгу по slug
  getBookBySlug(slug) {
    return api.get(`/products/slug/${slug}`)
  },
  
  // Получить рекомендованные книги (is_featured = true)
  getFeaturedBooks(limit = 8) {
    return this.getBooks({ featured: true, per_page: limit, sort_by: 'created_at', sort_order: 'desc' })
  },
  
  // Получить новинки (is_new = true)
  getNewBooks(limit = 8) {
    return this.getBooks({ new: true, per_page: limit, sort_by: 'created_at', sort_order: 'desc' })
  },
  
  // Получить бестселлеры (is_bestseller = true)
  getBestsellerBooks(limit = 8) {
    return this.getBooks({ bestseller: true, per_page: limit, sort_by: 'created_at', sort_order: 'desc' })
  },
  
  // Создать книгу
  createBook(data) {
    return api.post('/products', data)
  },
  
  // Обновить книгу
  updateBook(id, data) {
    return api.put(`/products/${id}`, data)
  },
  
  // Удалить книгу
  deleteBook(id) {
    return api.delete(`/products/${id}`)
  },
  
  // Переключить статус "Рекомендуемая"
  toggleFeatured(id) {
    return api.patch(`/products/${id}/toggle-featured`)
  },
  
  getSimilarBooks(id, limit = 6) {
    return api.get(`/products/${id}/similar`, { params: { limit } })
  },
  
  // Переключить статус активности
  toggleActive(id) {
    return api.patch(`/products/${id}/toggle-active`)
  },

  // Экспорт книг в Excel
  exportToExcel(params = {}) {
    return api.get('/products/export/excel', {
      params,
      responseType: 'blob'
    })
  }
}

export const bookImageApi = {
  // Получить изображения книги
  getImages(bookId) {
    return api.get(`/products/${bookId}/images`)
  },
  
  // Загрузить изображение
  uploadImage(bookId, data) {
    return api.post(`/products/${bookId}/images`, data, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  },
  
  // Обновить изображение
  updateImage(imageId, data) {
    return api.put(`/products-images/${imageId}`, data)
  },
  
  // Удалить изображение
  deleteImage(imageId) {
    return api.delete(`/products-images/${imageId}`)
  },
  
  // Установить как основное
  setPrimary(imageId) {
    return api.patch(`/products-images/${imageId}/set-primary`)
  },
  
  // Обновить порядок сортировки
  reorderImages(bookId, images) {
    return api.post(`/products/${bookId}/images/reorder`, { images })
  }
}