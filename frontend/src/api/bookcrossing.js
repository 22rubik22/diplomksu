import api from './axios'

export const bookcrossingApi = {
  // Публичные маршруты
  getBooks(params = {}) {
    return api.get('/bookcrossing', { params })
  },
  
  getBook(id) {
    return api.get(`/bookcrossing/${id}`)
  },

  getGenres() {
    return api.get('/bookcrossing/genres/list')
  },
  
  // Пользовательские маршруты
  addBook(data) {
    return api.post('/bookcrossing', data)
  },
  
  takeBook(id) {
    return api.post(`/bookcrossing/${id}/take`)
  },
  
  returnBook(id) {
    return api.post(`/bookcrossing/${id}/return`)
  },
  
  getMyBooks() {
    return api.get('/my/bookcrossing')
  },
  
  getMyTakenBooks() {
    return api.get('/my/taken-books')
  },
  
  // Админские маршруты
  updateBook(id, data) {
    return api.put(`/admin/bookcrossing/${id}`, data)
  },
  
  deleteBook(id) {
    return api.delete(`/admin/bookcrossing/${id}`)
  },
  
  forceReturn(id) {
    return api.post(`/admin/bookcrossing/${id}/force-return`)
  }
}