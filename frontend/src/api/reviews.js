import api from './axios'

export const reviewApi = {
  // Получить отзывы книги
  getBookReviews(bookId, params = {}) {
    return api.get(`/products/${bookId}/reviews`, { params })
  },
  
  // Получить информацию о возможности оставить отзыв
  getReviewPermission(bookId) {
    return api.get(`/products/${bookId}/review-permission`)
  },
  
  // Получить свой отзыв на книгу
  getUserReview(bookId) {
    return api.get(`/products/${bookId}/reviews/user`)
  },
  
  // Создать отзыв
  createReview(bookId, data) {
    return api.post(`/products/${bookId}/reviews`, data)
  },
  
  // Обновить отзыв
  updateReview(reviewId, data) {
    return api.put(`/reviews/${reviewId}`, data)
  },
  
  // Удалить отзыв
  deleteReview(reviewId) {
    return api.delete(`/reviews/${reviewId}`)
  },
  
  // ============= АДМИНСКИЕ МЕТОДЫ =============
  
  // Получить все отзывы
  getAllReviews(params = {}) {
    return api.get('/admin/reviews', { params })
  },
  
  // Одобрить отзыв
  approveReview(reviewId) {
    return api.patch(`/admin/reviews/${reviewId}/approve`)
  },
  
  // Отклонить отзыв
  rejectReview(reviewId) {
    return api.delete(`/admin/reviews/${reviewId}/reject`)
  }
}