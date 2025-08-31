import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  email: string
}

interface AuthState {
  user: User | null
  token: string | null
  roles: string[]
  permissions: string[]
  isAuthenticated: boolean
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    token: null,
    roles: [],
    permissions: [],
    isAuthenticated: false
  }),

  getters: {
    hasPermission: (state: AuthState) => (permission: string) => {
      return state.permissions.includes(permission)
    },
    hasRole: (state: AuthState) => (role: string) => {
      return state.roles.includes(role)
    }
  },

  actions: {
    async login(email: string, password: string) {
      console.log(email, password)
      try {
        
        const config = useRuntimeConfig()
        console.log(`${config.public.apiBase}/login`)
        const response = await $fetch(`${config.public.apiBase}/login`, {
          method: 'POST',
          body: { email, password }
        })
       
        this.setAuthData(response)
        return { success: true, data: response }
      } catch (error: any) {
        console.log(error)
        return { success: false, error: error.data?.error || 'Login failed' }
      }
    },

    async register(name: string, email: string, password: string) {
      try {
       
        const config = useRuntimeConfig()
        
        const response = await $fetch(`${config.public.apiBase}/register`, {
          method: 'POST',
          body: { name, email, password }
        })

        this.setAuthData(response)
        return { success: true, data: response }
      } catch (error: any) {
        return { success: false, error: error.data?.message || 'Registration failed' }
      }
    },

    async logout() {
      try {
        
        const config = useRuntimeConfig()
        
        if (this.token) {
          await $fetch(`${config.public.apiBase}/logout`, {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${this.token}`
            }
          })
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.clearAuthData()
      }
    },

    async fetchUser() {
      try {
      
        const config = useRuntimeConfig()
        
        if (!this.token) return false

        const response = await $fetch(`${config.public.apiBase}/user`, {
          headers: {
            'Authorization': `Bearer ${this.token}`
          }
        })

        this.setAuthData(response)
        return true
      } catch (error) {
        this.clearAuthData()
        return false
      }
    },

    setAuthData(data: any) {
      console.log(data)
      this.user = data.user
      this.token = data.token
      this.roles = Array.isArray(data.roles) ? data.roles : [data.roles]
      this.permissions = Array.isArray(data.permissions) ? data.permissions : []
      this.isAuthenticated = true

      // Store token in localStorage
      if (process.client) {
        localStorage.setItem('auth_token', data.token)
        localStorage.setItem('auth_user', JSON.stringify(data.user))
        localStorage.setItem('auth_roles', JSON.stringify(this.roles))
        localStorage.setItem('auth_permissions', JSON.stringify(this.permissions))
      }
    },

    clearAuthData() {
      this.user = null
      this.token = null
      this.roles = []
      this.permissions = []
      this.isAuthenticated = false

      // Remove token from localStorage
      if (process.client) {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
        localStorage.removeItem('auth_roles')
        localStorage.removeItem('auth_permissions')
      }
    },

    async initializeAuth() {
      if (process.client) {
        const token = localStorage.getItem('auth_token')
        const userStr = localStorage.getItem('auth_user')
        const rolesStr = localStorage.getItem('auth_roles')
        const permissionsStr = localStorage.getItem('auth_permissions')

        if (token && userStr) {
          this.token = token
          this.user = JSON.parse(userStr)
          this.roles = rolesStr ? JSON.parse(rolesStr) : []
          this.permissions = permissionsStr ? JSON.parse(permissionsStr) : []
          this.isAuthenticated = true

          // Verify token is still valid by fetching user data
          await this.fetchUser()
        }
      }
    }
  }
})
