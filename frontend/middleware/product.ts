export default defineNuxtRouteMiddleware((to) => {
const authStore = useAuthStore()

  // If user is not authenticated and trying to access protected route
  if (!authStore.isAuthenticated) {
    return navigateTo('/login')
  }
  const reqPermission = to.path.replace(/^\//, "").replace(/\//g, ".");
  console.log(reqPermission)
  if (reqPermission && !authStore.hasPermission(reqPermission)) {
    return navigateTo('/unauthorized')
  }

})
