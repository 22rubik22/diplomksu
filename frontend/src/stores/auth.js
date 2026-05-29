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
    }
  },
  
  getters: {
    userName: (state) => state.user?.name || '',
    userEmail: (state) => state.user?.email || '',
    userPhone: (state) => state.user?.phone || '',        // Добавлено
    userCity: (state) => state.user?.city || '',          // Добавлено
    userAddress: (state) => state.user?.address_line || '', // Добавлено
    userRole: (state) => state.user?.role || '',
    isAdmin: (state) => state.user?.role === 'admin'
  }
})