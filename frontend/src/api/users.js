import api from './axios'

export const userApi = {
  // Получить список пользователей (с фильтрацией и пагинацией)
  getUsers(params) {
    return api.get('/admin/users', { params })
  },
  
  // Получить информацию о конкретном пользователе
  getUser(id) {
    return api.get(`/admin/users/${id}`)
  },
  
  // Назначить роль пользователю
  assignRole(id, data) {
    return api.put(`/admin/users/${id}/role`, data)
  },
  
  // Заблокировать/разблокировать пользователя
  toggleBlock(id) {
    return api.post(`/admin/users/${id}/toggle-block`)
  },
  
  // Обновить данные пользователя (админом)
  updateUser(id, data) {
    return api.put(`/admin/users/${id}`, data)
  }
}