import api from './axios'

export const genreApi = {
  // Получить список жанров
  getGenres(params = {}) {
    return api.get('/categories', { params })
  },
  
  // Получить дерево жанров
  getGenreTree() {
    return api.get('/categories/tree')
  },
  
  // Получить один жанр
  getGenre(id) {
    return api.get(`/categories/${id}`)
  },
  
  // Создать жанр (с поддержкой FormData)
  createGenre(data) {
    // Если данные являются FormData, добавляем правильные заголовки
    if (data instanceof FormData) {
      return api.post('/categories', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    return api.post('/categories', data)
  },
  
  // Обновить жанр (с поддержкой FormData)
  updateGenre(id, data) {
    // Если данные являются FormData, используем POST с _method=PUT
    if (data instanceof FormData) {
      data.append('_method', 'PUT')
      return api.post(`/categories/${id}`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
    }
    return api.put(`/categories/${id}`, data)
  },
  
  // Удалить жанр
  deleteGenre(id) {
    return api.delete(`/categories/${id}`)
  },
  
  // Удалить изображение жанра
  deleteGenreImage(id) {
    return api.delete(`/categories/${id}/image`)
  },
  
  // Переключить активность
  toggleActive(id) {
    return api.patch(`/categories/${id}/toggle-active`)
  }
}