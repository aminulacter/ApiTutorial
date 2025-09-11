<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <NuxtLink to="/users" class="text-gray-400 hover:text-gray-600 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </NuxtLink>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ user?.name || 'User Details' }}</h1>
            <p class="mt-2 text-gray-600">View user information and manage roles</p>
          </div>
        </div>
        <div class="flex space-x-3">
          <NuxtLink
            :to="`/users/${userId}/edit`"
            class="btn-secondary"
            v-if="authStore.hasPermission('users.update')"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit User
          </NuxtLink>
        </div>
      </div>
    </div>

    <div v-if="usersStore.loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="!user" class="text-center py-8 text-gray-500">
      User not found
    </div>

    <div v-else class="space-y-6">
      <!-- User Information -->
      <div class="card">
        <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <p class="text-sm text-gray-900">{{ user.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <p class="text-sm text-gray-900">{{ user.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email Status</label>
            <div class="flex items-center">
              <span
                :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  user.email_verified_at
                    ? 'bg-green-100 text-green-800'
                    : 'bg-yellow-100 text-yellow-800'
                ]"
              >
                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
              </span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">User ID</label>
            <p class="text-sm text-gray-900">{{ user.id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
            <p class="text-sm text-gray-900">{{ formatDate(user.created_at) }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
            <p class="text-sm text-gray-900">{{ formatDate(user.updated_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Roles -->
      <div class="card">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Roles</h3>
          <button
            @click="showRoleModal = true"
            class="btn-primary"
            v-if="authStore.hasPermission('users.assign_roles')"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Manage Roles
          </button>
        </div>

        <div v-if="user.roles && user.roles.length > 0" class="space-y-3">
          <div
            v-for="role in user.roles"
            :key="role.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div>
              <h4 class="font-medium text-gray-900">{{ role.display_name || role.name }}</h4>
              <p v-if="role.description" class="text-sm text-gray-500">{{ role.description }}</p>
              <div v-if="role.permissions && role.permissions.length > 0" class="mt-2">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="permission in role.permissions.slice(0, 3)"
                    :key="permission.id"
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ permission.display_name || permission.name }}
                  </span>
                  <span
                    v-if="role.permissions.length > 3"
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-600"
                  >
                    +{{ role.permissions.length - 3 }} more
                  </span>
                </div>
              </div>
            </div>
            <button
              @click="removeRole(role)"
              class="text-red-600 hover:text-red-500"
              v-if="authStore.hasPermission('users.remove_roles')"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          No roles assigned
        </div>
      </div>

      <!-- Permissions Summary -->
      <div class="card">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions Summary</h3>
        <div v-if="user.permissions && user.permissions.length > 0" class="space-y-2">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            <span
              v-for="permission in user.permissions"
              :key="permission.id"
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
            >
              {{ permission.display_name || permission.name }}
            </span>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          No permissions assigned
        </div>
      </div>
    </div>

    <!-- Role Management Modal -->
    <div v-if="showRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Manage Roles</h3>

          <div v-if="rolesStore.loading" class="flex justify-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
          </div>

          <div v-else class="space-y-2 max-h-64 overflow-y-auto">
            <div
              v-for="role in rolesStore.roles"
              :key="role.id"
              class="flex items-center"
            >
              <input
                :id="`modal-role-${role.id}`"
                v-model="selectedRoles"
                :value="role.id"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label :for="`modal-role-${role.id}`" class="ml-3 text-sm text-gray-700">
                <span class="font-medium">{{ role.display_name || role.name }}</span>
                <span v-if="role.description" class="text-gray-500"> - {{ role.description }}</span>
              </label>
            </div>
          </div>

          <div class="flex justify-end space-x-4 mt-6">
            <button
              @click="showRoleModal = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
            <button
              @click="updateUserRoles"
              :disabled="usersStore.loading"
              class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50"
            >
              {{ usersStore.loading ? 'Updating...' : 'Update Roles' }}
            </button>
          </div>
        </div>
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

const showRoleModal = ref(false)
const selectedRoles = ref([])

// Computed
const user = computed(() => usersStore.currentUser)

// Methods
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const removeRole = async (role) => {
  if (!confirm(`Are you sure you want to remove the role "${role.display_name || role.name}" from this user?`)) {
    return
  }

  const result = await usersStore.removeRolesFromUser(userId.value, [role.id])

  if (result.success) {
    // Refresh user data
    await usersStore.fetchUser(userId.value)
  } else {
    console.error('Failed to remove role:', result.error)
  }
}

const updateUserRoles = async () => {
  const result = await usersStore.updateUser(userId.value, {
    name: user.value.name,
    email: user.value.email,
    roles: selectedRoles.value
  })

  if (result.success) {
    showRoleModal.value = false
    // Refresh user data
    await usersStore.fetchUser(userId.value)
  } else {
    console.error('Failed to update roles:', result.error)
  }
}

// Lifecycle
onMounted(async () => {
  if (userId.value && userId.value > 0) {
    await Promise.all([
      usersStore.fetchUser(userId.value),
      rolesStore.fetchRoles()
    ])

    // Set initial selected roles
    if (user.value?.roles) {
      selectedRoles.value = user.value.roles.map(role => role.id)
    }
  }
})

// Watch for user changes to update selected roles
watch(user, (newUser) => {
  if (newUser?.roles) {
    selectedRoles.value = newUser.roles.map(role => role.id)
  }
})
</script>
