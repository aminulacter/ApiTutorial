<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <NuxtLink to="/roles" class="text-gray-400 hover:text-gray-600 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </NuxtLink>
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ role?.display_name || role?.name || 'Role Details' }}</h1>
            <p class="mt-2 text-gray-600">View role information and manage permissions</p>
          </div>
        </div>
        <div class="flex space-x-3">
          <NuxtLink
            :to="`/roles/edit/${role?.id}`"
            class="btn-secondary"
            v-if="authStore.hasPermission('roles.update')"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Role
          </NuxtLink>
        </div>
      </div>
    </div>

    <div v-if="rolesStore.loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="!role" class="text-center py-8 text-gray-500">
      Role not found
    </div>

    <div v-else class="space-y-6">
      <!-- Role Information -->
      <div class="card">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Role Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
            <p class="text-sm text-gray-900">{{ role.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
            <p class="text-sm text-gray-900">{{ role.display_name || 'Not set' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Role ID</label>
            <p class="text-sm text-gray-900">{{ role.id }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Created</label>
            <p class="text-sm text-gray-900">{{ formatDate(role.created_at) }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <p class="text-sm text-gray-900">{{ role.description || 'No description provided' }}</p>
          </div>
        </div>
      </div>

      <!-- Permissions -->
      <div class="card">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium text-gray-900">Permissions</h3>
          <button
            @click="showPermissionModal = true"
            class="btn-primary"
            v-if="authStore.hasPermission('roles.assign_permissions')"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Manage Permissions
          </button>
        </div>

        <div v-if="role.permissions && role.permissions.length > 0" class="space-y-3">
          <div
            v-for="permission in role.permissions"
            :key="permission.id"
            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
          >
            <div>
              <h4 class="font-medium text-gray-900">{{ permission.display_name || permission.name }}</h4>
              <p v-if="permission.description" class="text-sm text-gray-500">{{ permission.description }}</p>
            </div>
            <button
              @click="removePermission(permission)"
              class="text-red-600 hover:text-red-500"
              v-if="authStore.hasPermission('roles.remove_permissions')"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <div v-else class="text-center py-8 text-gray-500">
          No permissions assigned
        </div>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Role Statistics</h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Total Permissions</span>
              <span class="text-sm font-medium text-gray-900">{{ role.permissions_count || 0 }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Users with this Role</span>
              <span class="text-sm font-medium text-gray-900">{{ role.users_count || 0 }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Created</span>
              <span class="text-sm font-medium text-gray-900">{{ formatDate(role.created_at) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Last Updated</span>
              <span class="text-sm font-medium text-gray-900">{{ formatDate(role.updated_at) }}</span>
            </div>
          </div>
        </div>

        <div class="card">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
          <div class="space-y-3">
            <NuxtLink
              :to="`/roles/${role.id}/edit`"
              class="flex items-center p-3 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors"
              v-if="authStore.hasPermission('roles.update')"
            >
              <svg class="w-5 h-5 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              <span class="font-medium text-primary-700">Edit Role</span>
            </NuxtLink>

            <button
              @click="showPermissionModal = true"
              class="flex items-center p-3 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors w-full"
              v-if="authStore.hasPermission('roles.assign_permissions')"
            >
              <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="font-medium text-blue-700">Manage Permissions</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Permission Management Modal -->
    <div v-if="showPermissionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Manage Permissions</h3>

          <div v-if="rolesStore.loading" class="flex justify-center py-4">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600"></div>
          </div>

          <div v-else class="space-y-2 max-h-64 overflow-y-auto">
            <div
              v-for="permission in rolesStore.permissions"
              :key="permission.id"
              class="flex items-center"
            >
              <input
                :id="`modal-permission-${permission.id}`"
                v-model="selectedPermissions"
                :value="permission.id"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label :for="`modal-permission-${permission.id}`" class="ml-3 text-sm text-gray-700">
                <span class="font-medium">{{ permission.display_name || permission.name }}</span>
                <span v-if="permission.description" class="text-gray-500"> - {{ permission.description }}</span>
              </label>
            </div>
          </div>

          <div class="flex justify-end space-x-4 mt-6">
            <button
              @click="showPermissionModal = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
            <button
              @click="updateRolePermissions"
              :disabled="rolesStore.loading"
              class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 disabled:opacity-50"
            >
              {{ rolesStore.loading ? 'Updating...' : 'Update Permissions' }}
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
const rolesStore = useRolesStore()

// Fix: Use optional chaining and provide a default
const roleId = parseInt(route.params?.id)
const showPermissionModal = ref(false)
const selectedPermissions = ref([])

// Computed
const role = computed(() => rolesStore.currentRole)

// Methods
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const removePermission = async (permission) => {
  if (!confirm(`Are you sure you want to remove the permission "${permission.display_name || permission.name}" from this role?`)) {
    return
  }

  const result = await rolesStore.detachPermissionsFromRole(roleId, [permission.id])

  if (result.success) {
    // Refresh role data
    await rolesStore.fetchRole(roleId)
  } else {
    console.error('Failed to remove permission:', result.error)
  }
}

const updateRolePermissions = async () => {
  const result = await rolesStore.updateRole(roleId, {
    name: role.value.name,
    display_name: role.value.display_name,
    description: role.value.description,
    permissions: selectedPermissions.value
  })

  if (result.success) {
    showPermissionModal.value = false
    // Refresh role data
    await rolesStore.fetchRole(roleId)
  } else {
    console.error('Failed to update permissions:', result.error)
  }
}

// Lifecycle
onMounted(async () => {
  await Promise.all([
    rolesStore.fetchRole(roleId),
    rolesStore.fetchPermissions()
  ])

  // Set initial selected permissions
  if (role.value?.permissions) {
    selectedPermissions.value = role.value.permissions.map(permission => permission.id)
  }
})

// Watch for role changes to update selected permissions
watch(role, (newRole) => {
  if (newRole?.permissions) {
    selectedPermissions.value = newRole.permissions.map(permission => permission.id)
  }
})
</script>
