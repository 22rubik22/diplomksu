import api from './axios'

export const eventApi = {
  // Публичные маршруты
  getEvents(params = {}) {
    return api.get('/events', { params })
  },
  
  getEvent(id) {
    return api.get(`/events/${id}`)
  },
  
  getEventBySlug(slug) {
    return api.get(`/events/slug/${slug}`)
  },
  
  // Админские маршруты
  getAllEvents(params = {}) {
    return api.get('/admin/events', { params })
  },
  
  createEvent(data) {
    return api.post('/admin/events', data)
  },
  
  updateEvent(id, data) {
    return api.put(`/admin/events/${id}`, data)
  },
  
  deleteEvent(id) {
    return api.delete(`/admin/events/${id}`)
  },
  
  // Регистрации
  getEventRegistrations(eventId) {
    return api.get(`/admin/events/${eventId}/registrations`)
  },
  
  confirmRegistration(registrationId) {
    return api.patch(`/admin/registrations/${registrationId}/confirm`)
  },
  
  // Получить мои регистрации
  getMyRegistrations() {
    return api.get('/my/registrations')
  },
  
  // Зарегистрироваться на мероприятие
  registerForEvent(eventId, data) {
    return api.post(`/events/${eventId}/register`, data)
  },
  
  // Отменить регистрацию
  cancelRegistration(eventId) {
    return api.delete(`/events/${eventId}/register`)
  },
  
  // Проверить регистрацию
  checkRegistration(eventId) {
    return api.get(`/events/${eventId}/check-registration`)
  }
}