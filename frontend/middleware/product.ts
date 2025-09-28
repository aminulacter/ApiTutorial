export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()
  const token = useCookie('auth_token').value
  // Check if user is authenticated
  if (!authStore.isAuthenticated || !token) {
    return navigateTo('/login')
  }

  // Verify token is still valid
  if (authStore.token || token) {
    try {
      // Import token validation utility
      const { validateTokenExpiration } = await import('~/utils/tokenValidation')
      
      // Quick client-side validation first
      const validation = validateTokenExpiration(authStore.token || token)
      
      if (!validation.isValid) {
        console.log('Token expired in product middleware, clearing auth data')
        authStore.clearAuthData()
        return navigateTo('/login')
      }
    } catch (error: any) {
      console.error('Token validation error in product middleware:', error)
      // On error, clear auth data and redirect to login for security
      authStore.clearAuthData()
      return navigateTo('/login')
    }
  } else {
    // No token available, redirect to login
    authStore.clearAuthData()
    return navigateTo('/login')
  }

  // Check permissions
  const reqPermission = to.path.replace(/^\//, "").replace(/\//g, ".")
  console.log('Required permission:', reqPermission)
  
  if (reqPermission && !authStore.hasPermission(reqPermission)) {
    return navigateTo('/unauthorized')
  }
})
