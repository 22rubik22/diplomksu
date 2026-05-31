import { defineStore } from 'pinia'
import api from '@/api/axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    isAuthenticated: false,
    loading: false
  }),
  
  actions: {
    async register(userData) {
      this.loading = true
      try {
        const response = await api.post('/register', userData)
        if (response.data.success) {
          this.user = response.data.user
          this.isAuthenticated = true
          return { success: true }
        }
      } catch (error) {
        console.error('Register error:', error)
        return {
          success: false,
          errors: error.response?.data?.errors || { general: ['Ошибка регистрации'] }
        }
      } finally {
        this.loading = false
      }
    },
    
    async login(credentials) {
      this.loading = true
      try {
        const response = await api.post('/login', credentials)
        if (response.data.success) {
          this.user = response.data.user
          this.isAuthenticated = true
          return { success: true }
        }
      } catch (error) {
        console.error('Login error:', error)
        return {
          success: false,
          message: error.response?.data?.message || 'Ошибка входа'
        }
      } finally {
        this.loading = false
      }
    },
    
    async logout() {
      try {
        await api.post('/logout')
        this.user = null
        this.isAuthenticated = false
      } catch (error) {
        console.error('Logout error:', error)
      }
    },
    
    async checkAuth() {
      try {
        const response = await api.get('/check')
        this.isAuthenticated = response.data.authenticated
        this.user = response.data.user
        return response.data.authenticated
      } catch (error) {
        console.error('Check auth error:', error)
        this.isAuthenticated = false
        this.user = null
        return false
      }
    },
    
    async fetchUser() {
      if (!this.isAuthenticated) return
      try {
        const response = await api.get('/me')
        if (response.data.success) {
          this.user = response.data.user
        }
      } catch (error) {
        console.error('Error fetching user:', error)
      }
    },
    
    async updateProfile(data) {
      this.loading = true
      try {
        const response = await api.put('/profile', data)
        if (response.data.success) {
          this.user = response.data.user
          return { 
            success: true, 
            message: response.data.message 
          }
        }
      } catch (error) {
        console.error('Update profile error:', error)
        return {
          success: false,
          message: error.response?.data?.message || 'Ошибка обновления профиля',
          errors: error.response?.data?.errors
        }
      } finally {
        this.loading = false
      }
    },
    
    async changePassword(data) {
      this.loading = true
      try {
        const response = await api.post('/change-password', data)
        if (response.data.success) {
          return { 
            success: true, 
            message: response.data.message 
          }
        }
      } catch (error) {
        console.error('Change password error:', error)
        return {
          success: false,
          message: error.response?.data?.message || 'Ошибка смены пароля',
          errors: error.response?.data?.errors
        }
      } finally {
        this.loading = false
      }
    },
    
    // ========== НОВЫЕ МЕТОДЫ ДЛЯ АВАТАРА ==========
    
    /**
     * Обновление аватара пользователя
     */
    async updateAvatar(file) {
      this.loading = true
      try {
        const formData = new FormData()
        formData.append('avatar', file)
        
        const response = await api.post('/profile/avatar', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        if (response.data.success) {
          // Обновляем данные пользователя с новым аватаром
          if (this.user) {
            this.user.avatar = response.data.avatar_url
          }
          return { 
            success: true, 
            message: response.data.message,
            avatar_url: response.data.avatar_url
          }
        }
      } catch (error) {
        console.error('Update avatar error:', error)
        return {
          success: false,
          message: error.response?.data?.message || 'Ошибка обновления аватара',
          errors: error.response?.data?.errors
        }
      } finally {
        this.loading = false
      }
    },
    
    /**
     * Удаление аватара пользователя
     */
    async deleteAvatar() {
      this.loading = true
      try {
        const response = await api.delete('/profile/avatar')
        
        if (response.data.success) {
          // Обновляем данные пользователя (аватар станет null)
          if (this.user) {
            this.user.avatar = null
          }
          return { 
            success: true, 
            message: response.data.message,
            avatar_url: response.data.avatar_url
          }
        }
      } catch (error) {
        console.error('Delete avatar error:', error)
        return {
          success: false,
          message: error.response?.data?.message || 'Ошибка удаления аватара',
          errors: error.response?.data?.errors
        }
      } finally {
        this.loading = false
      }
    }
  },
  
  getters: {
    userName: (state) => state.user?.name || '',
    userEmail: (state) => state.user?.email || '',
    userPhone: (state) => state.user?.phone || '',
    userCity: (state) => state.user?.city || '',
    userAddress: (state) => state.user?.address_line || '',
    userPostalCode: (state) => state.user?.postal_code || '',
    userRole: (state) => state.user?.role || '',
    userAvatar: (state) => state.user?.avatar || null,
    isAdmin: (state) => state.user?.role === 'admin',
    isManager: (state) => state.user?.role === 'manager' || state.user?.role === 'admin',
    isYandexUser: (state) => !!state.user?.yandex_id
  }
})