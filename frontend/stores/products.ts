import { defineStore } from 'pinia'

interface Product {
  id: number
  name: string
  description?: string
  price: number
  stock: number
  sku?: string
  image?: string
  category?: string
  brand?: string
  is_active: boolean
  created_at: string
  updated_at: string
}

interface ProductsState {
  products: Product[]
  currentProduct: Product | null
  loading: boolean
  error: string | null
  pagination: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  filters: {
    category?: string
    brand?: string
    min_price?: number
    max_price?: number
    in_stock?: string
    is_active?: boolean
    search?: string
    sort_by?: string
    sort_order?: string
  }
}

export const useProductsStore = defineStore('products', {
  state: (): ProductsState => ({
    products: [],
    currentProduct: null,
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
    hasProducts: (state) => state.products.length > 0,
    totalProducts: (state) => state.pagination.total
  },

  actions: {
    async fetchProducts(params: any = {}) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const queryParams = new URLSearchParams()

        // Add pagination params
        if (params.page) queryParams.append('per_page', params.per_page || '10')
        if (params.page) queryParams.append('page', params.page.toString())

        // Add filter params
        Object.entries(params).forEach(([key, value]) => {
          if (value !== undefined && value !== null && key !== 'page' && key !== 'per_page') {
            queryParams.append(key, value.toString())
          }
        })

        const result = await apiCall(`/products?${queryParams}`)

        if (result.success) {
          this.products = result.data.data.products
          this.pagination = {
            current_page: result.data.data.current_page,
            last_page: result.data.data.last_page,
            per_page: result.data.data.per_page,
            total: result.data.data.total
          }
          this.filters = result.data.data.filters_applied || {}
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to fetch products'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchProduct(id: number) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/products/${id}`)

        if (result.success) {
          this.currentProduct = result.data.data
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to fetch product'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async createProduct(productData: FormData) {
      this.loading = true
      this.error = null
      console.log("submit product data")
      
      try {
        const { apiCall } = useApi()

        const result = await apiCall('/products', {
          method: 'POST',
          body: productData
        })

        if (result.success) {
          // Refresh products list
          await this.fetchProducts()
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to create product'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async updateProduct(id: number, productData: FormData) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/products/${id}`, {
          method: 'POST',
          body: productData
        })

        if (result.success) {
          // Update the product in the list
          const index = this.products.findIndex(p => p.id === id)
          if (index !== -1) {
            this.products[index] = result.data.data
          }

          if (this.currentProduct?.id === id) {
            this.currentProduct = result.data.data
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to update product'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async deleteProduct(id: number) {
      this.loading = true
      this.error = null

      try {
        const { apiCall } = useApi()

        const result = await apiCall(`/products/${id}`, {
          method: 'DELETE'
        })

        if (result.success) {
          // Remove from products list
          this.products = this.products.filter(p => p.id !== id)

          if (this.currentProduct?.id === id) {
            this.currentProduct = null
          }
        } else {
          this.error = result.error
        }

        return result
      } catch (error: any) {
        this.error = 'Failed to delete product'
        return { success: false, error: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchCategories() {
      try {
        const { apiCall } = useApi()

        const result = await apiCall('/products/categories')

        return result.success 
          ? { success: true, data: result.data.data }
          : { success: false, error: result.error }
      } catch (error: any) {
        return { success: false, error: 'Failed to fetch categories' }
      }
    },

    async fetchBrands() {
      try {
        const { apiCall } = useApi()

        const result = await apiCall('/products/brands')

        return result.success 
          ? { success: true, data: result.data.data }
          : { success: false, error: result.error }
      } catch (error: any) {
        return { success: false, error: 'Failed to fetch brands' }
      }
    },

    clearError() {
      this.error = null
    },

    clearCurrentProduct() {
      this.currentProduct = null
    }
  }
})
