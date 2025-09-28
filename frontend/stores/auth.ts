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
  persist: true,
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
      } catch (error: any) {
        // If token is expired or invalid, clear auth data
        if (error.status === 401 || error.statusCode === 401) {
          console.log('Token expired during user fetch, clearing auth data')
          this.clearAuthData()
          return false
        }
        this.clearAuthData()
        return false
      }
    },

    setAuthData(data: any) {
      console.log(data)
      useCookie('auth_token').value = data.token
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
        try {
          const token = localStorage.getItem('auth_token')
          const userStr = localStorage.getItem('auth_user')
          const rolesStr = localStorage.getItem('auth_roles')
          const permissionsStr = localStorage.getItem('auth_permissions')

          if (token && userStr) {
            // First, validate token expiration client-side
            const { validateTokenExpiration } = await import('~/utils/tokenValidation')
            const tokenValidation = validateTokenExpiration(token)
            
            if (!tokenValidation.isValid) {
              console.log('Token expired during initialization, clearing auth data')
              this.clearAuthData()
              return
            }

            this.token = token
            
            // Safely parse JSON data with error handling
            try {
              this.user = JSON.parse(userStr)
            } catch (e) {
              console.error('Error parsing user data from localStorage:', e)
              this.clearAuthData()
              return
            }

            try {
              this.roles = rolesStr ? JSON.parse(rolesStr) : []
            } catch (e) {
              console.error('Error parsing roles data from localStorage:', e)
              this.roles = []
            }

            try {
              this.permissions = permissionsStr ? JSON.parse(permissionsStr) : []
            } catch (e) {
              console.error('Error parsing permissions data from localStorage:', e)
              this.permissions = []
            }

            this.isAuthenticated = true

            // Verify token is still valid by fetching user data (server-side validation)
            const isValid = await this.fetchUser()
            if (!isValid) {
              // Token is invalid, redirect to login
              await navigateTo('/login')
            }
          }
        } catch (error) {
          console.error('Error during auth initialization:', error)
          this.clearAuthData()
        }
      }
    }
  }
})
