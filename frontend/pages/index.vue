<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
      <p class="mt-2 text-gray-600">Welcome back, {{ authStore.user?.name }}!</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Products Card -->
      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Products</p>
            <p class="text-2xl font-semibold text-gray-900">{{ productsStore.totalProducts }}</p>
          </div>
        </div>
      </div>

      <!-- Users Card -->
      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Users</p>
            <p class="text-2xl font-semibold text-gray-900">{{ usersStore.totalUsers }}</p>
          </div>
        </div>
      </div>

      <!-- Roles Card -->
      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Roles</p>
            <p class="text-2xl font-semibold text-gray-900">{{ rolesStore.totalRoles }}</p>
          </div>
        </div>
      </div>

      <!-- Active Products Card -->
      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Active Products</p>
            <p class="text-2xl font-semibold text-gray-900">{{ activeProductsCount }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Recent Products -->
      <div class="card">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Products</h3>
        <div v-if="productsStore.loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
        </div>
        <div v-else-if="recentProducts.length === 0" class="text-center py-8 text-gray-500">
          No products found
        </div>
        <div v-else class="space-y-3">
          <div 
            v-for="product in recentProducts" 
            :key="product.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div class="flex items-center">
              <img 
                v-if="product.image" 
                :src="product.image" 
                :alt="product.name"
                class="w-10 h-10 object-cover rounded-lg mr-3"
              />
              <div v-else class="w-10 h-10 bg-gray-200 rounded-lg mr-3 flex items-center justify-center">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <div>
                <p class="font-medium text-gray-900">{{ product.name }}</p>
                <p class="text-sm text-gray-500">${{ product.price }}</p>
              </div>
            </div>
            <NuxtLink 
              :to="`/products/${product.id}`"
              class="text-primary-600 hover:text-primary-500 text-sm font-medium"
            >
              View
            </NuxtLink>
          </div>
        </div>
        <div class="mt-4">
          <NuxtLink to="/products" class="text-primary-600 hover:text-primary-500 text-sm font-medium">
            View all products →
          </NuxtLink>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="space-y-3">
          <!-- Product Actions -->
          <NuxtLink 
            to="/products/create"
            class="flex items-center p-3 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors"
            v-if="authStore.hasPermission('products.create')"
          >
            <svg class="w-5 h-5 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span class="font-medium text-primary-700">Add New Product</span>
          </NuxtLink>
          
          <NuxtLink 
            to="/products"
            class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            v-if="authStore.hasPermission('products.view')"
          >
            <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span class="font-medium text-gray-700">Manage Products</span>
          </NuxtLink>

          <!-- User Actions -->
          <NuxtLink 
            to="/users/create"
            class="flex items-center p-3 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
            v-if="authStore.hasPermission('users.create')"
          >
            <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span class="font-medium text-green-700">Add New User</span>
          </NuxtLink>
          
          <NuxtLink 
            to="/users"
            class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            v-if="authStore.hasPermission('users.view')"
          >
            <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            <span class="font-medium text-gray-700">Manage Users</span>
          </NuxtLink>

          <!-- Role Actions -->
          <NuxtLink 
            to="/roles/create"
            class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
            v-if="authStore.hasPermission('roles.create')"
          >
            <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            <span class="font-medium text-blue-700">Add New Role</span>
          </NuxtLink>
          
          <NuxtLink 
            to="/roles"
            class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors"
            v-if="authStore.hasPermission('roles.view')"
          >
            <svg class="w-5 h-5 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span class="font-medium text-gray-700">Manage Roles</span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const authStore = useAuthStore()
const productsStore = useProductsStore()
const usersStore = useUsersStore()
const rolesStore = useRolesStore()

// Fetch data on page load
onMounted(async () => {
  await Promise.all([
    productsStore.fetchProducts({ per_page: 5 }),
    usersStore.fetchUsers({ per_page: 1 }), // Just to get total count
    rolesStore.fetchRoles({ per_page: 1 }) // Just to get total count
  ])
})

// Computed properties for dashboard stats
const activeProductsCount = computed(() => {
  return productsStore.products.filter(p => p.is_active).length
})

const recentProducts = computed(() => {
  return productsStore.products.slice(0, 5)
})
</script>
