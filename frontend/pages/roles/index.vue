<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Roles</h1>
          <p class="mt-2 text-gray-600">Manage user roles and permissions</p>
        </div>
        <NuxtLink
          to="/roles/create"
          class="btn-primary"
          v-if="authStore.hasPermission('roles.create')"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add Role
        </NuxtLink>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search roles..."
            class="input-field"
            @input="debouncedSearch"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
          <select v-model="filters.sort_by" class="input-field" @change="applyFilters">
            <option value="created_at">Created Date</option>
            <option value="name">Name</option>
            <option value="display_name">Display Name</option>
            <option value="updated_at">Updated Date</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
          <select v-model="filters.sort_order" class="input-field" @change="applyFilters">
            <option value="desc">Newest First</option>
            <option value="asc">Oldest First</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Roles Table -->
    <div class="card">
      <div v-if="rolesStore.loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <div v-else-if="!rolesStore.hasRoles" class="text-center py-8 text-gray-500">
        No roles found
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="role in rolesStore.roles" :key="role.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-blue-600">
                        {{ (role.display_name || role.name).charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ role.display_name || role.name }}</div>
                    <div class="text-sm text-gray-500">{{ role.name }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">{{ role.description || 'No description' }}</div>
              </td>
              <td class="px-6 py-4">
                <div v-if="role.permissions_count > 0" class="text-sm text-gray-900">
                  {{ role.permissions_count }} permission{{ role.permissions_count !== 1 ? 's' : '' }}
                </div>
                <div v-else class="text-sm text-gray-500">No permissions</div>
              </td>
              <td class="px-6 py-4">
                <div v-if="role.users_count > 0" class="text-sm text-gray-900">
                  {{ role.users_count }} user{{ role.users_count !== 1 ? 's' : '' }}
                </div>
                <div v-else class="text-sm text-gray-500">No users</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(role.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <NuxtLink
                    :to="`/roles/${role.id}`"
                    class="text-primary-600 hover:text-primary-500"
                    v-if="authStore.hasPermission('roles.view')"
                  >
                    View
                  </NuxtLink>
                  <NuxtLink
                    :to="`/roles/edit/${role.id}`"
                    class="text-indigo-600 hover:text-indigo-500"
                    v-if="authStore.hasPermission('roles.edit')"
                  >
                    Edit
                  </NuxtLink>
                  <button
                    @click="confirmDeleteRole(role)"
                    class="text-red-600 hover:text-red-500"
                    v-if="authStore.hasPermission('roles.delete')"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="rolesStore.hasRoles" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button
            @click="goToPage(rolesStore.pagination.current_page - 1)"
            :disabled="rolesStore.pagination.current_page <= 1"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
          >
            Previous
          </button>
          <button
            @click="goToPage(rolesStore.pagination.current_page + 1)"
            :disabled="rolesStore.pagination.current_page >= rolesStore.pagination.last_page"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ (rolesStore.pagination.current_page - 1) * rolesStore.pagination.per_page + 1 }}</span>
              to
              <span class="font-medium">{{ Math.min(rolesStore.pagination.current_page * rolesStore.pagination.per_page, rolesStore.pagination.total) }}</span>
              of
              <span class="font-medium">{{ rolesStore.pagination.total }}</span>
              results
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
              <button
                v-for="page in visiblePages"
                :key="page"
                @click="goToPage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                  page === rolesStore.pagination.current_page
                    ? 'z-10 bg-primary-50 border-primary-500 text-primary-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">Delete Role</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Are you sure you want to delete the role <strong>{{ roleToDelete?.display_name || roleToDelete?.name }}</strong>?
              This action cannot be undone.
            </p>
          </div>
          <div class="flex justify-center space-x-4 mt-4">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
            <button
              @click="deleteRole"
              :disabled="rolesStore.loading"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
            >
              {{ rolesStore.loading ? 'Deleting...' : 'Delete' }}
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

const authStore = useAuthStore()
const rolesStore = useRolesStore()

// Reactive data
const filters = reactive({
  search: '',
  sort_by: 'created_at',
  sort_order: 'desc'
})

const showDeleteModal = ref(false)
const roleToDelete = ref(null)

// Computed properties
const visiblePages = computed(() => {
  const current = rolesStore.pagination.current_page
  const last = rolesStore.pagination.last_page
  const pages = []

  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }

  return pages
})

// Methods
const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString()
}

const debouncedSearch = useDebounceFn(() => {
  applyFilters()
}, 500)

const applyFilters = async () => {
  await rolesStore.fetchRoles({
    ...filters,
    page: 1
  })
}

const goToPage = async (page) => {
  if (page >= 1 && page <= rolesStore.pagination.last_page) {
    await rolesStore.fetchRoles({
      ...filters,
      page
    })
  }
}

const confirmDeleteRole = (role) => {
  roleToDelete.value = role
  showDeleteModal.value = true
}

const deleteRole = async () => {
  if (!roleToDelete.value) return

  const result = await rolesStore.deleteRole(roleToDelete.value.id)

  if (result.success) {
    showDeleteModal.value = false
    roleToDelete.value = null
    // Show success message
  } else {
    // Show error message
    console.error('Failed to delete role:', result.error)
  }
}

// Lifecycle
onMounted(async () => {
  await rolesStore.fetchRoles(filters)
})
</script>
