<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center">
        <NuxtLink to="/users" class="text-gray-400 hover:text-gray-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </NuxtLink>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Create User</h1>
          <p class="mt-2 text-gray-600">Add a new user to the system</p>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="card">
      <form @submit.prevent="createUser" class="space-y-6">
        <!-- Basic Information -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Full Name *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="input-field"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Enter full name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address *
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                required
                class="input-field"
                :class="{ 'border-red-500': errors.email }"
                placeholder="Enter email address"
              />
              <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
            </div>
          </div>
        </div>

        <!-- Password -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Password</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                Password *
              </label>
              <input
                id="password"
                v-model="form.password"
                type="password"
                required
                class="input-field"
                :class="{ 'border-red-500': errors.password }"
                placeholder="Enter password"
              />
              <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
            </div>

            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                Confirm Password *
              </label>
              <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                required
                class="input-field"
                :class="{ 'border-red-500': errors.password_confirmation }"
                placeholder="Confirm password"
              />
              <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-600">{{ errors.password_confirmation }}</p>
            </div>
          </div>
        </div>

        <!-- Roles -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Roles</h3>
          <div v-if="rolesStore.loading" class="flex justify-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
          </div>
          <div v-else-if="!rolesStore.hasRoles" class="text-center py-4 text-gray-500">
            No roles available
          </div>
          <div v-else class="space-y-2">
            <div 
              v-for="role in rolesStore.roles" 
              :key="role.id"
              class="flex items-center"
            >
              <input
                :id="`role-${role.id}`"
                v-model="form.roles"
                :value="role.id"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label :for="`role-${role.id}`" class="ml-3 text-sm text-gray-700">
                <span class="font-medium">{{ role.display_name || role.name }}</span>
                <span v-if="role.description" class="text-gray-500"> - {{ role.description }}</span>
              </label>
            </div>
          </div>
          <p v-if="errors.roles" class="mt-1 text-sm text-red-600">{{ errors.roles }}</p>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
          <NuxtLink to="/users" class="btn-secondary">
            Cancel
          </NuxtLink>
          <button 
            type="submit" 
            :disabled="usersStore.loading"
            class="btn-primary"
          >
            {{ usersStore.loading ? 'Creating...' : 'Create User' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const authStore = useAuthStore()
const usersStore = useUsersStore()
const rolesStore = useRolesStore()

// Form data
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: []
})

const errors = reactive({})

// Methods
const createUser = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key])

  // Basic validation
  if (!form.name.trim()) {
    errors.name = 'Name is required'
  }
  if (!form.email.trim()) {
    errors.email = 'Email is required'
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Email is invalid'
  }
  if (!form.password) {
    errors.password = 'Password is required'
  } else if (form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters'
  }
  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match'
  }

  // If there are errors, don't submit
  if (Object.keys(errors).length > 0) {
    return
  }

  const result = await usersStore.createUser(form)

  if (result.success) {
    // Redirect to users list
    await navigateTo('/users')
  } else {
    // Handle API errors
    if (result.error && typeof result.error === 'object') {
      Object.assign(errors, result.error)
    } else {
      errors.general = result.error || 'Failed to create user'
    }
  }
}

// Lifecycle
onMounted(async () => {
  await rolesStore.fetchRoles()
})
</script>
