import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest'
  }
})

// Добавляем CSRF токен для защиты (если используется в Laravel)
api.interceptors.request.use(config => {
  // Пытаемся получить CSRF токен из мета-тега
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  if (token) {
    config.headers['X-CSRF-TOKEN'] = token
  }
  return config
})

// Интерцептор для обработки ошибок
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 419) {
      // CSRF token mismatch - можно перенаправить на страницу входа
      console.error('Session expired')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api