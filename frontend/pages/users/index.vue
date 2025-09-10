<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Users</h1>
          <p class="mt-2 text-gray-600">Manage user accounts and permissions</p>
        </div>
        <NuxtLink
          to="/users/create"
          class="btn-primary"
          v-if="authStore.hasPermission('users.create')"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add User
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
            placeholder="Search users..."
            class="input-field"
            @input="debouncedSearch"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
          <select v-model="filters.sort_by" class="input-field" @change="applyFilters">
            <option value="created_at">Created Date</option>
            <option value="name">Name</option>
            <option value="email">Email</option>
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

    <!-- Users Table -->
    <div class="card">
      <div v-if="usersStore.loading" class="flex justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
      </div>

      <div v-else-if="!usersStore.hasUsers" class="text-center py-8 text-gray-500">
        No users found
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in usersStore.users" :key="user.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                      <span class="text-sm font-medium text-primary-600">
                        {{ user.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                    <div class="text-sm text-gray-500">ID: {{ user.id }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ user.email }}</div>
                <div v-if="user.email_verified_at" class="text-sm text-green-600">Verified</div>
                <div v-else class="text-sm text-yellow-600">Unverified</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div v-if="user.roles && user.roles.length > 0" class="flex flex-wrap gap-1">
                  <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
                  >
                    {{ role.display_name || role.name }}
                  </span>
                </div>
                <div v-else class="text-sm text-gray-500">No roles</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(user.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <NuxtLink
                    :to="`/users/${user.id}`"
                    class="text-primary-600 hover:text-primary-500 p-1 rounded hover:bg-primary-50"
                    v-if="authStore.hasPermission('users.view')"
                    title="View"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </NuxtLink>
                  <NuxtLink
                    :to="`/users/edit/${user.id}`"
                    class="text-indigo-600 hover:text-indigo-500 p-1 rounded hover:bg-indigo-50"
                    v-if="authStore.hasPermission('users.edit')"
                    title="Edit"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </NuxtLink>
                  <button
                    @click="confirmDeleteUser(user)"
                    class="text-red-600 hover:text-red-500 p-1 rounded hover:bg-red-50"
                    v-if="authStore.hasPermission('users.delete') && user.id !== authStore.user?.id"
                    title="Delete"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="usersStore.hasUsers" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
        <div class="flex-1 flex justify-between sm:hidden">
          <button
            @click="goToPage(usersStore.pagination.current_page - 1)"
            :disabled="usersStore.pagination.current_page <= 1"
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
          >
            Previous
          </button>
          <button
            @click="goToPage(usersStore.pagination.current_page + 1)"
            :disabled="usersStore.pagination.current_page >= usersStore.pagination.last_page"
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
          >
            Next
          </button>
        </div>
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ (usersStore.pagination.current_page - 1) * usersStore.pagination.per_page + 1 }}</span>
              to
              <span class="font-medium">{{ Math.min(usersStore.pagination.current_page * usersStore.pagination.per_page, usersStore.pagination.total) }}</span>
              of
              <span class="font-medium">{{ usersStore.pagination.total }}</span>
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
                  page === usersStore.pagination.current_page
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
          <h3 class="text-lg font-medium text-gray-900 mt-4">Delete User</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Are you sure you want to delete <strong>{{ userToDelete?.name }}</strong>?
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
              @click="deleteUser"
              :disabled="usersStore.loading"
              class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:opacity-50"
            >
              {{ usersStore.loading ? 'Deleting...' : 'Delete' }}
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
const usersStore = useUsersStore()
const { showSuccess, showError } = useToastNotification()

// Reactive data
const filters = reactive({
  search: '',
  sort_by: 'created_at',
  sort_order: 'desc'
})

const showDeleteModal = ref(false)
const userToDelete = ref(null)

// Computed properties
const visiblePages = computed(() => {
  const current = usersStore.pagination.current_page
  const last = usersStore.pagination.last_page
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
  await usersStore.fetchUsers({
    ...filters,
    page: 1
  })
}

const goToPage = async (page) => {
  if (page >= 1 && page <= usersStore.pagination.last_page) {
    await usersStore.fetchUsers({
      ...filters,
      page
    })
  }
}

const confirmDeleteUser = (user) => {
  userToDelete.value = user
  showDeleteModal.value = true
}

const deleteUser = async () => {
  if (!userToDelete.value) return

  const userName = userToDelete.value.name
  const result = await usersStore.deleteUser(userToDelete.value.id)

  if (result.success) {
    showDeleteModal.value = false
    userToDelete.value = null
    showSuccess(`User "${userName}" has been deleted successfully`)
  } else {
    showError(result.error || 'Failed to delete user')
    console.error('Failed to delete user:', result.error)
  }
}

// Lifecycle
onMounted(async () => {
  await usersStore.fetchUsers(filters)
})
</script>
