<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Products</h1>
          <p class="mt-2 text-gray-600">Manage your product inventory</p>
        </div>
        <NuxtLink to="/products/create" class="btn-primary">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Add Product
        </NuxtLink>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input
            v-model="filters.search"
            type="text"
            class="input-field"
            placeholder="Search products..."
            @input="debouncedSearch"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select v-model="filters.category" class="input-field" @change="applyFilters">
            <option value="">All Categories</option>
            <option v-for="category in categories" :key="category" :value="category">
              {{ category }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
          <select v-model="filters.brand" class="input-field" @change="applyFilters">
            <option value="">All Brands</option>
            <option v-for="brand in brands" :key="brand" :value="brand">
              {{ brand }}
            </option>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Stock Status</label>
          <select v-model="filters.in_stock" class="input-field" @change="applyFilters">
            <option value="">All</option>
            <option value="true">In Stock</option>
            <option value="false">Out of Stock</option>
          </select>
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
          <input
            v-model.number="filters.min_price"
            type="number"
            step="0.01"
            class="input-field"
            placeholder="0.00"
            @change="applyFilters"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
          <input
            v-model.number="filters.max_price"
            type="number"
            step="0.01"
            class="input-field"
            placeholder="1000.00"
            @change="applyFilters"
          />
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sort By</label>
          <select v-model="filters.sort_by" class="input-field" @change="applyFilters">
            <option value="created_at">Date Created</option>
            <option value="name">Name</option>
            <option value="price">Price</option>
            <option value="updated_at">Last Updated</option>
          </select>
        </div>
      </div>
      
      <div class="mt-4 flex justify-between items-center">
        <button @click="clearFilters" class="btn-secondary">
          Clear Filters
        </button>
        <div class="text-sm text-gray-500">
          {{ productsStore.totalProducts }} products found
        </div>
      </div>
    </div>

    <!-- Products Grid -->
    <div v-if="productsStore.loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="productsStore.products.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">No products</h3>
      <p class="mt-1 text-sm text-gray-500">Get started by creating a new product.</p>
      <div class="mt-6">
        <NuxtLink to="/products/create" class="btn-primary">
          Add Product
        </NuxtLink>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div 
        v-for="product in productsStore.products" 
        :key="product.id"
        class="card hover:shadow-lg transition-shadow cursor-pointer"
        @click="navigateToProduct(product.id)"
      >
        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200 mb-4">
          <img 
            v-if="product.image" 
            :src="product.image" 
            :alt="product.name"
            class="h-full w-full object-cover object-center"
          />
          <div v-else class="h-full w-full flex items-center justify-center">
            <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
          </div>
        </div>
        
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">{{ product.name }}</h3>
          <p class="text-sm text-gray-500 mb-2 line-clamp-2">{{ product.description }}</p>
          
          <div class="flex justify-between items-center mb-2">
            <span class="text-lg font-bold text-gray-900">${{ product.price }}</span>
            <span 
              :class="[
                'px-2 py-1 text-xs font-medium rounded-full',
                product.stock > 10 ? 'bg-green-100 text-green-800' : 
                product.stock > 0 ? 'bg-yellow-100 text-yellow-800' : 
                'bg-red-100 text-red-800'
              ]"
            >
              {{ product.stock > 0 ? `${product.stock} in stock` : 'Out of stock' }}
            </span>
          </div>
          
          <div class="flex justify-between items-center text-sm text-gray-500">
            <span>{{ product.category || 'No category' }}</span>
            <span>{{ product.brand || 'No brand' }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="productsStore.pagination.last_page > 1" class="mt-8 flex justify-center">
      <nav class="flex items-center space-x-2">
        <button 
          @click="changePage(productsStore.pagination.current_page - 1)"
          :disabled="productsStore.pagination.current_page === 1"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        
        <button 
          v-for="page in visiblePages" 
          :key="page"
          @click="changePage(page)"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-md',
            page === productsStore.pagination.current_page
              ? 'bg-primary-600 text-white'
              : 'text-gray-500 bg-white border border-gray-300 hover:bg-gray-50'
          ]"
        >
          {{ page }}
        </button>
        
        <button 
          @click="changePage(productsStore.pagination.current_page + 1)"
          :disabled="productsStore.pagination.current_page === productsStore.pagination.last_page"
          class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </nav>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth'
})

const productsStore = useProductsStore()
const router = useRouter()
const { showError } = useToastNotification()

const filters = ref({
  search: '',
  category: '',
  brand: '',
  min_price: null,
  max_price: null,
  in_stock: '',
  sort_by: 'created_at',
  sort_order: 'desc',
  page: 1
})

const categories = ref([])
const brands = ref([])

// Debounced search
const debouncedSearch = useDebounceFn(() => {
  applyFilters()
}, 500)

// Fetch initial data
onMounted(async () => {
  await Promise.all([
    fetchProducts(),
    fetchCategories(),
    fetchBrands()
  ])
})

const fetchProducts = async () => {
console.log(filters.value)
  const params = { ...filters.value }
  Object.keys(params).forEach(key => {
    if (params[key] === '' || params[key] === null) {
      delete params[key]
    }
  })
  
  try {
    await productsStore.fetchProducts(params)
  } catch (error) {
    showError('Failed to fetch products')
    console.error('Error fetching products:', error)
  }
}

const fetchCategories = async () => {
  try {
    const result = await productsStore.fetchCategories()
    if (result.success) {
      categories.value = result.data
    } else {
      showError('Failed to fetch categories')
    }
  } catch (error) {
    showError('Failed to fetch categories')
    console.error('Error fetching categories:', error)
  }
}

const fetchBrands = async () => {
  try {
    const result = await productsStore.fetchBrands()
    if (result.success) {
      brands.value = result.data
    } else {
      showError('Failed to fetch brands')
    }
  } catch (error) {
    showError('Failed to fetch brands')
    console.error('Error fetching brands:', error)
  }
}

const applyFilters = () => {
  fetchProducts()
}

const clearFilters = () => {
  filters.value = {
    search: '',
    category: '',
    brand: '',
    min_price: null,
    max_price: null,
    in_stock: '',
    sort_by: 'created_at',
    sort_order: 'desc'
  }
  fetchProducts()
}

const changePage = (page) => {
  if (page >= 1 && page <= productsStore.pagination.last_page) {
    filters.value.page = page
    fetchProducts()
  }
}

const navigateToProduct = (id) => {
  router.push(`/products/${id}`)
}

// Computed properties for pagination
const visiblePages = computed(() => {
  const current = productsStore.pagination.current_page
  const last = productsStore.pagination.last_page
  const delta = 2
  
  const range = []
  const rangeWithDots = []
  
  for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
    range.push(i)
  }
  
  if (current - delta > 2) {
    rangeWithDots.push(1, '...')
  } else {
    rangeWithDots.push(1)
  }
  
  rangeWithDots.push(...range)
  
  if (current + delta < last - 1) {
    rangeWithDots.push('...', last)
  } else {
    rangeWithDots.push(last)
  }
  
  return rangeWithDots.filter((item, index, arr) => {
    if (item === '...') return true
    return arr.indexOf(item) === index
  })
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
