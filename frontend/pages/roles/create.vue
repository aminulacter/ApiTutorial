<template>
  <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center">
        <NuxtLink to="/roles" class="text-gray-400 hover:text-gray-600 mr-4">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </NuxtLink>
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Create Role</h1>
          <p class="mt-2 text-gray-600">Add a new role to the system</p>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="card">
      <form @submit.prevent="createRole" class="space-y-6">
        <!-- Basic Information -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                Role Name *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="input-field"
                :class="{ 'border-red-500': errors.name }"
                placeholder="e.g., manager"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label for="display_name" class="block text-sm font-medium text-gray-700 mb-2">
                Display Name
              </label>
              <input
                id="display_name"
                v-model="form.display_name"
                type="text"
                class="input-field"
                :class="{ 'border-red-500': errors.display_name }"
                placeholder="e.g., Manager"
              />
              <p v-if="errors.display_name" class="mt-1 text-sm text-red-600">{{ errors.display_name }}</p>
            </div>
          </div>
          
          <div class="mt-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
              Description
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="input-field"
              :class="{ 'border-red-500': errors.description }"
              placeholder="Describe the role's purpose and responsibilities"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
          </div>
        </div>

        <!-- Permissions -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
          <div v-if="rolesStore.loading" class="flex justify-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
          </div>
          <div v-else-if="!rolesStore.hasPermissions" class="text-center py-4 text-gray-500">
            No permissions available
          </div>
          <div v-else class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div 
                v-for="permission in rolesStore.permissions" 
                :key="permission.id"
                class="flex items-center"
              >
                <input
                  :id="`permission-${permission.id}`"
                  v-model="form.permissions"
                  :value="permission.id"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label :for="`permission-${permission.id}`" class="ml-3 text-sm text-gray-700">
                  <span class="font-medium">{{ permission.display_name || permission.name }}</span>
                  <span v-if="permission.description" class="text-gray-500"> - {{ permission.description }}</span>
                </label>
              </div>
            </div>
          </div>
          <p v-if="errors.permissions" class="mt-1 text-sm text-red-600">{{ errors.permissions }}</p>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
          <NuxtLink to="/roles" class="btn-secondary">
            Cancel
          </NuxtLink>
          <button 
            type="submit" 
            :disabled="rolesStore.loading"
            class="btn-primary"
          >
            {{ rolesStore.loading ? 'Creating...' : 'Create Role' }}
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
const rolesStore = useRolesStore()

// Form data
const form = reactive({
  name: '',
  display_name: '',
  description: '',
  permissions: []
})

const errors = reactive({})

// Methods
const createRole = async () => {
  // Clear previous errors
  Object.keys(errors).forEach(key => delete errors[key])

  // Basic validation
  if (!form.name.trim()) {
    errors.name = 'Role name is required'
  }

  // If there are errors, don't submit
  if (Object.keys(errors).length > 0) {
    return
  }

  const result = await rolesStore.createRole(form)

  if (result.success) {
    // Redirect to roles list
    await navigateTo('/roles')
  } else {
    // Handle API errors
    if (result.error && typeof result.error === 'object') {
      Object.assign(errors, result.error)
    } else {
      errors.general = result.error || 'Failed to create role'
    }
  }
}

// Lifecycle
onMounted(async () => {
  await rolesStore.fetchPermissions()
})
</script>
