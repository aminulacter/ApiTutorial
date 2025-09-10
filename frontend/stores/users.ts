import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  email: string
  email_verified_at?: string
  created_at: string
  updated_at: string
  roles?: Role[]
  permissions?: Permission[]
}

interface Role {
  id: number
  name: string
  display_name?: string
  permissions?: Permission[]
}

interface Permission {
  id: number
  name: string
  display_name?: string
}

interface UsersState {
  users: User[]
  currentUser: User | null
  loading: boolean
  error: string | null
  pagination: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters: {
    search?: string
    role?: string
    sort_by?: string
    sort_order?: string
  }
}

export const useUsersStore = defineStore('users', {
  state: (): UsersState => ({
    users: [],
    currentUser: null,
    loading: false,
    error: null,
    pagination: {
      current_page: 1,
      last_page: 1,
      per_page: 10,
      total: 0
    },
    filters: {}
  }),

  getters: {
    hasUsers: (state) => state.users.length > 0,
    totalUsers: (state) => state.pagination.total
  },

  actions: {
    async fetchUsers(params: any = {}) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const queryParams = new URLSearchParams()

        // Add pagination params
        if (params.page) queryParams.append('page', params.page.toString())
        if (params.per_page) queryParams.append('per_page', params.per_page.toString())

        // Add filter params
        Object.entries(params).forEach(([key, value]) => {
          if (value !== undefined && value !== null && key !== 'page' && key !== 'per_page') {
            queryParams.append(key, value.toString())
          }
        })

        const result = await apiCall(`/users?${queryParams}`)

        if (result.success) {
          this.users = result.data.data.users
          this.pagination = {
            current_page: result.data.data.current_page,
            last_page: result.data.data.last_page,
            per_page: result.data.data.per_page,
            total: result.data.data.total
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to fetch users'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchUser(id: number) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/users/${id}?with_roles=true`)

        if (result.success) {
          this.currentUser = result.data.data
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to fetch user'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async createUser(userData: any) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall('/users', {
          method: 'POST',
          body: userData
        })

        if (result.success) {
          // Refresh users list
          await this.fetchUsers()
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to create user'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateUser(id: number, userData: any) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/users/${id}`, {
          method: 'PUT',
          body: userData
        })

        if (result.success) {
          // Update the user in the list
          const index = this.users.findIndex(u => u.id === id)
          if (index !== -1) {
            this.users[index] = result.data.data
          }

          if (this.currentUser?.id === id) {
            this.currentUser = result.data.data
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to update user'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteUser(id: number) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/users/${id}`, {
          method: 'DELETE'
        })

        if (result.success) {
          // Remove from users list
          this.users = this.users.filter(u => u.id !== id)

          if (this.currentUser?.id === id) {
            this.currentUser = null
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to delete user'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async assignRolesToUser(userId: number, roleIds: number[]) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/users/${userId}/assign-roles`, {
          method: 'POST',
          body: { roles: roleIds }
        })

        if (result.success) {
          // Update the user in the list
          const index = this.users.findIndex(u => u.id === userId)
          if (index !== -1) {
            this.users[index] = result.data.data
          }

          if (this.currentUser?.id === userId) {
            this.currentUser = result.data.data
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to assign roles'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async removeRolesFromUser(userId: number, roleIds: number[]) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/users/${userId}/remove-roles`, {
          method: 'DELETE',
          body: { roles: roleIds }
        })

        if (result.success) {
          // Update the user in the list
          const index = this.users.findIndex(u => u.id === userId)
          if (index !== -1) {
            this.users[index] = result.data.data
          }

          if (this.currentUser?.id === userId) {
            this.currentUser = result.data.data
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to remove roles'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    },

    clearCurrentUser() {
      this.currentUser = null
    }
  }
})
