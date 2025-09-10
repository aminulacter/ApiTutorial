import { defineStore } from 'pinia'

interface Role {
  id: number
  name: string
  display_name?: string
  description?: string
  created_at: string
  updated_at: string
  permissions?: Permission[]
  users_count?: number
  permissions_count?: number
}

interface Permission {
  id: number
  name: string
  display_name?: string
  description?: string
}

interface RolesState {
  roles: Role[]
  currentRole: Role | null
  permissions: Permission[]
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
    sort_by?: string
    sort_order?: string
  }
}

export const useRolesStore = defineStore('roles', {
  state: (): RolesState => ({
    roles: [],
    currentRole: null,
    permissions: [],
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
    hasRoles: (state) => state.roles.length > 0,
    totalRoles: (state) => state.pagination.total,
    hasPermissions: (state) => state.permissions.length > 0
  },

  actions: {
    async fetchRoles(params: any = {}) {
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

        const result = await apiCall(`/roles?${queryParams}`)

        if (result.success) {
          this.roles = result.data.data.roles
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
        this.error = 'Failed to fetch roles'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchRole(id: number) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/roles/${id}?with_permissions=true`)

        if (result.success) {
          this.currentRole = result.data.data
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to fetch role'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async createRole(roleData: any) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall('/roles', {
          method: 'POST',
          body: roleData
        })

        if (result.success) {
          // Refresh roles list
          await this.fetchRoles()
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to create role'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateRole(id: number, roleData: any) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/roles/${id}`, {
          method: 'PUT',
          body: roleData
        })

        if (result.success) {
          // Update the role in the list
          const index = this.roles.findIndex(r => r.id === id)
          if (index !== -1) {
            this.roles[index] = result.data.data
          }

          if (this.currentRole?.id === id) {
            this.currentRole = result.data.data
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to update role'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteRole(id: number) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/roles/${id}`, {
          method: 'DELETE'
        })

        if (result.success) {
          // Remove from roles list
          this.roles = this.roles.filter(r => r.id !== id)

          if (this.currentRole?.id === id) {
            this.currentRole = null
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to delete role'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async attachPermissionsToRole(roleId: number, permissionIds: number[]) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/roles/${roleId}/attach-permissions`, {
          method: 'POST',
          body: { permissions: permissionIds }
        })

        if (result.success) {
          // Update the role in the list
          const index = this.roles.findIndex(r => r.id === roleId)
          if (index !== -1) {
            this.roles[index] = result.data.data
          }

          if (this.currentRole?.id === roleId) {
            this.currentRole = result.data.data
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to attach permissions'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async detachPermissionsFromRole(roleId: number, permissionIds: number[]) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/roles/${roleId}/detach-permissions`, {
          method: 'DELETE',
          body: { permissions: permissionIds }
        })

        if (result.success) {
          // Update the role in the list
          const index = this.roles.findIndex(r => r.id === roleId)
          if (index !== -1) {
            this.roles[index] = result.data.data
          }

          if (this.currentRole?.id === roleId) {
            this.currentRole = result.data.data
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to detach permissions'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchPermissions() {
      try {
        // For now, we'll create a mock list of permissions
        // In a real app, you might have a dedicated permissions endpoint
        const mockPermissions = [
          { id: 1, name: 'users.view', display_name: 'View Users' },
          { id: 2, name: 'users.create', display_name: 'Create Users' },
          { id: 3, name: 'users.update', display_name: 'Update Users' },
          { id: 4, name: 'users.delete', display_name: 'Delete Users' },
          { id: 5, name: 'roles.view', display_name: 'View Roles' },
          { id: 6, name: 'roles.create', display_name: 'Create Roles' },
          { id: 7, name: 'roles.update', display_name: 'Update Roles' },
          { id: 8, name: 'roles.delete', display_name: 'Delete Roles' },
          { id: 9, name: 'roles.assign_permissions', display_name: 'Assign Permissions' },
          { id: 10, name: 'roles.remove_permissions', display_name: 'Remove Permissions' },
          { id: 11, name: 'products.view', display_name: 'View Products' },
          { id: 12, name: 'products.create', display_name: 'Create Products' },
          { id: 13, name: 'products.update', display_name: 'Update Products' },
          { id: 14, name: 'products.delete', display_name: 'Delete Products' }
        ]

        this.permissions = mockPermissions
        return { success: true, data: mockPermissions }
      } catch (error: any) {
        return { success: false, error: error.data?.message || 'Failed to fetch permissions' }
      }
    },

    clearError() {
      this.error = null
    },

    clearCurrentRole() {
      this.currentRole = null
    }
  }
})
