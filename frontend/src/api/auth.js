import api from './axios'

export const authApi = {
  // Регистрация
  register(data) {
    return api.post('/register', data)
  },
  
  // Вход
  login(data) {
    return api.post('/login', data)
  },
  
  // Выход
  logout() {
    return api.post('/logout')
  },
  
  // Получить текущего пользователя
  getCurrentUser() {
    return api.get('/me')
  },
  
  // Проверить авторизацию
  checkAuth() {
    return api.get('/check')
  },
  
  // Обновить профиль
  updateProfile(data) {
    return api.put('/profile', data)
  },
  
  // Сменить пароль
  changePassword(data) {
    return api.post('/change-password', data)
  },
  
  // ============= YANDEX AUTH =============
  
  // Получить URL для авторизации через Яндекс
  getYandexAuthUrl() {
    return api.get('/auth/yandex/url')
  },

  
  
  // Обработка callback от Яндекса
  handleYandexCallback(code, state) {
    return api.get('/auth/yandex/callback', {
      params: { code, state }
    })
  }
}