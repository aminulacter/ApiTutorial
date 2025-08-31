<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <nav v-if="authStore.isAuthenticated" class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <NuxtLink to="/" class="text-xl font-bold text-primary-600">
              Product Manager
            </NuxtLink>
          </div>
          
          <div class="flex items-center space-x-4">
            <NuxtLink 
              to="/products" 
              class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium"
            >
              Products
            </NuxtLink>
            <NuxtLink 
              to="/products/create" 
              class="text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium"
            >
              Add Product
            </NuxtLink>
            
            <div class="relative">
              <button 
                @click="showUserMenu = !showUserMenu"
                class="flex items-center text-gray-700 hover:text-primary-600 px-3 py-2 rounded-md text-sm font-medium"
              >
                {{ authStore.user?.name }}
                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              
              <div 
                v-if="showUserMenu"
                class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50"
              >
                <button 
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  Logout
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main>
      <NuxtPage />
    </main>

    <!-- Global loading overlay -->
    <div 
      v-if="globalLoading" 
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Loading...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const authStore = useAuthStore()
const route = useRoute()
const showUserMenu = ref(false)
const globalLoading = ref(false)

// Initialize auth on app start
onMounted(async () => {
  await authStore.initializeAuth()
})

// Watch for route changes to close user menu
watch(() => route.path, () => {
  showUserMenu.value = false
})

// Handle logout
const handleLogout = async () => {
  await authStore.logout()
  await navigateTo('/login')
}

// Global loading state
provide('setGlobalLoading', (loading) => {
  globalLoading.value = loading
})
</script>
