<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center">
        <NuxtLink :to="`/users/${userId}`" class="text-gray-400 hover:text-gray-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </NuxtLink>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Edit User</h1>
          <p class="mt-2 text-gray-600">Update user information and roles</p>
        </div>
      </div>
    </div>

    <div v-if="usersStore.loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="usersStore.error" class="text-center py-8">
      <div class="text-red-600 mb-4">{{ usersStore.error }}</div>
      <button @click="retryLoad" class="btn-primary">Retry</button>
    </div>

    <div v-else-if="!user" class="text-center py-8 text-gray-500">
      User not found
    </div>

    <div v-else>
      <!-- Form -->
      <div class="card">
        <form @submit.prevent="updateUser" class="space-y-6">
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

          <!-- Password (Optional) -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Password</h3>
            <p class="text-sm text-gray-500 mb-4">Leave blank to keep current password</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                  New Password
                </label>
                <input
                  id="password"
                  v-model="form.password"
                  type="password"
                  class="input-field"
                  :class="{ 'border-red-500': errors.password }"
                  placeholder="Enter new password"
                />
                <p v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password }}</p>
              </div>

              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                  Confirm New Password
                </label>
                <input
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  class="input-field"
                  :class="{ 'border-red-500': errors.password_confirmation }"
                  placeholder="Confirm new password"
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
            <NuxtLink :to="`/users/${userId}`" class="btn-secondary">
              Cancel
            </NuxtLink>
            <button
              type="submit"
              :disabled="usersStore.loading"
              class="btn-primary"
            >
              {{ usersStore.loading ? 'Updating...' : 'Update User' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const route = useRoute()
const authStore = useAuthStore()
const usersStore = useUsersStore()
const rolesStore = useRolesStore()

// Fix: Handle route params properly
const userId = computed(() => {
  const id = route.params?.id
  return Array.isArray(id) ? parseInt(id[0]) : parseInt(id || '0')
})

// Form data
const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: []
})

const errors = reactive({})

// Computed
const user = computed(() => usersStore.currentUser)

// Methods
const retryLoad = async () => {
  if (userId.value && userId.value > 0) {
    await usersStore.fetchUser(userId.value)
  }
}

const updateUser = async () => {
  console.log('Updating user:', userId.value, form)
  
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
  if (form.password && form.password.length < 8) {
    errors.password = 'Password must be at least 8 characters'
  }
  if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Passwords do not match'
  }

  // If there are errors, don't submit
  if (Object.keys(errors).length > 0) {
    console.log('Validation errors:', errors)
    return
  }

  // Prepare update data
  const updateData = {
    name: form.name,
    email: form.email,
    roles: form.roles
  }

  // Only include password if provided
  if (form.password) {
    updateData.password = form.password
    updateData.password_confirmation = form.password_confirmation
  }

  console.log('Sending update data:', updateData)
  const result = await usersStore.updateUser(userId.value, updateData)
  console.log('Update result:', result)

  if (result.success) {
    // Redirect to user details
    await navigateTo(`/users/${userId.value}`)
  } else {
    // Handle API errors
    if (result.error && typeof result.error === 'object') {
      Object.assign(errors, result.error)
    } else {
      errors.general = result.error || 'Failed to update user'
    }
    console.log('Update failed:', errors)
  }
}

// Lifecycle
onMounted(async () => {
  console.log('User edit page mounted, userId:', userId.value)
  
  if (userId.value && userId.value > 0) {
    await Promise.all([
      usersStore.fetchUser(userId.value),
      rolesStore.fetchRoles()
    ])

    // Populate form with user data
    if (user.value) {
      form.name = user.value.name
      form.email = user.value.email
      form.roles = user.value.roles ? user.value.roles.map(role => role.id) : []
      console.log('Form populated:', form)
    }
  } else {
    console.error('Invalid userId:', userId.value)
  }
})

// Watch for user changes to update form
watch(user, (newUser) => {
  if (newUser) {
    form.name = newUser.name
    form.email = newUser.email
    form.roles = newUser.roles ? newUser.roles.map(role => role.id) : []
    console.log('Form updated from user change:', form)
  }
})
</script>
