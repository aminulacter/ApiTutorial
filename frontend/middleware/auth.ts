export default defineNuxtRouteMiddleware(async (to) => {
  const authStore = useAuthStore()

  // Skip token validation for login and register pages
  if (to.path === '/login' || to.path === '/register') {
    // If user is already authenticated, redirect to home
    if (authStore.isAuthenticated) {
      return navigateTo('/')
    }
    return
  }

  // For protected routes, check authentication
  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }

  // If user is authenticated, verify token is still valid
  if (authStore.token) {
    try {
      // Import token validation utility
      const { validateTokenExpiration } = await import('~/utils/tokenValidation')
      
      // Quick client-side validation first
      const validation = validateTokenExpiration(authStore.token)
      
      if (!validation.isValid) {
        console.log('Token expired in middleware, clearing auth data')
        authStore.clearAuthData()
        return navigateTo('/login')
      }
      
      // Token appears valid, continue to the route
      return
    } catch (error: any) {
      console.error('Token validation error in auth middleware:', error)
      // On error, clear auth data and redirect to login for security
      authStore.clearAuthData()
      return navigateTo('/login')
    }
  } else {
    // No token available, redirect to login
    authStore.clearAuthData()
    return navigateTo('/login')
  }
})
