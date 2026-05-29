import { ref } from 'vue'

export function useToast() {
  const toast = ref({
    show: false,
    message: '',
    type: 'info' // info, success, error, warning
  })
  
  const showToast = (message, type = 'info', duration = 3000) => {
    toast.value = {
      show: true,
      message,
      type
    }
    
    setTimeout(() => {
      toast.value.show = false
    }, duration)
  }
  
  const success = (message, duration = 3000) => {
    showToast(message, 'success', duration)
  }
  
  const error = (message, duration = 3000) => {
    showToast(message, 'error', duration)
  }
  
  const warning = (message, duration = 3000) => {
    showToast(message, 'warning', duration)
  }
  
  const info = (message, duration = 3000) => {
    showToast(message, 'info', duration)
  }
  
  return {
    toast,
    showToast,
    success,
    error,
    warning,
    info
  }
}