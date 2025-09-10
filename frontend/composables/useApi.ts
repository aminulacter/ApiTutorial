export const useApi = () => {
  const authStore = useAuthStore()
  const config = useRuntimeConfig()

  const apiCall = async (url: string, options: any = {}) => {
    try {
      // Add authorization header if token exists
      const headers = {
        ...options.headers,
        ...(authStore.token && { 'Authorization': `Bearer ${authStore.token}` })
      }

      const response = await $fetch(url, {
        ...options,
        headers,
        baseURL: config.public.apiBase
      })

      return { success: true, data: response }
    } catch (error: any) {
      // Check if error is due to token expiration (401 Unauthorized)
      if (error.status === 401 || error.statusCode === 401) {
        console.log('Token expired, logging out user')
        await authStore.logout()
        
        // Redirect to login page
        if (process.client) {
          await navigateTo('/login')
        }
        
        return { success: false, error: 'Session expired. Please login again.', expired: true }
      }

      // Handle other errors
      const errorMessage = error.data?.message || error.data?.error || error.message || 'An error occurred'
      return { success: false, error: errorMessage }
    }
  }

  return {
    apiCall
  }
}
