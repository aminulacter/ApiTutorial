export default defineNuxtRouteMiddleware((to) => {
  const authStore = useAuthStore()
  
  // If user is not authenticated and trying to access protected route
  if (!authStore.isAuthenticated && to.path !== '/login' && to.path !== '/register') {
    return navigateTo('/login')
  }
  
  // If user is authenticated and trying to access auth pages
  if (authStore.isAuthenticated && (to.path === '/login' || to.path === '/register')) {
    return navigateTo('/')
  }
})
