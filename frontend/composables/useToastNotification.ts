import { useToast } from 'vue-toastification'

export const useToastNotification = () => {
  const toast = useToast()

  const showSuccess = (message: string, title?: string) => {
    toast.success(message, {
      title: title || 'Success',
      icon: {
        iconClass: 'fas fa-check-circle',
        iconTag: 'i'
      }
    })
  }

  const showError = (message: string, title?: string) => {
    toast.error(message, {
      title: title || 'Error',
      icon: {
        iconClass: 'fas fa-exclamation-circle',
        iconTag: 'i'
      }
    })
  }

  const showWarning = (message: string, title?: string) => {
    toast.warning(message, {
      title: title || 'Warning',
      icon: {
        iconClass: 'fas fa-exclamation-triangle',
        iconTag: 'i'
      }
    })
  }

  const showInfo = (message: string, title?: string) => {
    toast.info(message, {
      title: title || 'Info',
      icon: {
        iconClass: 'fas fa-info-circle',
        iconTag: 'i'
      }
    })
  }

  const showLoading = (message: string, title?: string) => {
    return toast.loading(message, {
      title: title || 'Loading...',
      icon: {
        iconClass: 'fas fa-spinner fa-spin',
        iconTag: 'i'
      }
    })
  }

  const dismissToast = (toastId: string) => {
    toast.dismiss(toastId)
  }

  const clearAll = () => {
    toast.clear()
  }

  return {
    showSuccess,
    showError,
    showWarning,
    showInfo,
    showLoading,
    dismissToast,
    clearAll
  }
}
