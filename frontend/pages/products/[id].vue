<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div v-if="productsStore.loading" class="flex justify-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600"></div>
    </div>

    <div v-else-if="!productsStore.currentProduct" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.34 0-4.29-1.009-5.824-2.562M15 6.3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Product not found</h3>
      <p class="mt-1 text-sm text-gray-500">The product you're looking for doesn't exist.</p>
      <div class="mt-6">
        <NuxtLink to="/products" class="btn-primary">
          Back to Products
        </NuxtLink>
      </div>
    </div>

    <div v-else>
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ productsStore.currentProduct.name }}</h1>
            <p class="mt-2 text-gray-600">Product Details</p>
          </div>
          <div class="flex space-x-3">
            <button
              @click="toggleEditMode"
              class="btn-secondary"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              {{ isEditMode ? 'Cancel Edit' : 'Edit Product' }}
            </button>
            <button
              @click="showDeleteModal = true"
              class="btn-danger"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
              </svg>
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Product Form/Details -->
      <div class="card">
        <div v-if="!isEditMode" class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Product Image -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Product Image</h3>
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-200">
              <img 
                v-if="productsStore.currentProduct.image" 
                :src="productsStore.currentProduct.image" 
                :alt="productsStore.currentProduct.name"
                class="h-full w-full object-cover object-center"
              />
              <div v-else class="h-full w-full flex items-center justify-center">
                <svg class="h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Product Details -->
          <div class="space-y-6">
            <div>
              <h3 class="text-lg font-medium text-gray-900 mb-4">Product Information</h3>
              <dl class="space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Name</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ productsStore.currentProduct.name }}</dd>
                </div>
                
                <div>
                  <dt class="text-sm font-medium text-gray-500">Description</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ productsStore.currentProduct.description || 'No description' }}</dd>
                </div>
                
                <div>
                  <dt class="text-sm font-medium text-gray-500">Price</dt>
                  <dd class="mt-1 text-lg font-bold text-gray-900">${{ productsStore.currentProduct.price }}</dd>
                </div>
                
                <div>
                  <dt class="text-sm font-medium text-gray-500">Stock</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    <span 
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        productsStore.currentProduct.stock > 10 ? 'bg-green-100 text-green-800' : 
                        productsStore.currentProduct.stock > 0 ? 'bg-yellow-100 text-yellow-800' : 
                        'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ productsStore.currentProduct.stock }} units
                    </span>
                  </dd>
                </div>
                
                <div v-if="productsStore.currentProduct.sku">
                  <dt class="text-sm font-medium text-gray-500">SKU</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ productsStore.currentProduct.sku }}</dd>
                </div>
                
                <div v-if="productsStore.currentProduct.category">
                  <dt class="text-sm font-medium text-gray-500">Category</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ productsStore.currentProduct.category }}</dd>
                </div>
                
                <div v-if="productsStore.currentProduct.brand">
                  <dt class="text-sm font-medium text-gray-500">Brand</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ productsStore.currentProduct.brand }}</dd>
                </div>
                
                <div>
                  <dt class="text-sm font-medium text-gray-500">Status</dt>
                  <dd class="mt-1 text-sm text-gray-900">
                    <span 
                      :class="[
                        'px-2 py-1 text-xs font-medium rounded-full',
                        productsStore.currentProduct.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ productsStore.currentProduct.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </dd>
                </div>
                
                <div>
                  <dt class="text-sm font-medium text-gray-500">Created</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(productsStore.currentProduct.created_at) }}</dd>
                </div>
                
                <div>
                  <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(productsStore.currentProduct.updated_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
        <form v-else @submit.prevent="handleSubmit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
              <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Product Name *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="input-field"
                :class="{ 'border-red-500': errors.name }"
                placeholder="Enter product name"
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
            </div>

            <div class="md:col-span-2">
              <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                Description
              </label>
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                class="input-field"
                :class="{ 'border-red-500': errors.description }"
                placeholder="Enter product description"
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
            </div>

            <div>
              <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                Price *
              </label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-gray-500">$</span>
                <input
                  id="price"
                  v-model.number="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="input-field pl-8"
                  :class="{ 'border-red-500': errors.price }"
                  placeholder="0.00"
                />
              </div>
              <p v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price }}</p>
            </div>

            <div>
              <label for="stock" class="block text-sm font-medium text-gray-700 mb-1">
                Stock Quantity *
              </label>
              <input
                id="stock"
                v-model.number="form.stock"
                type="number"
                min="0"
                required
                class="input-field"
                :class="{ 'border-red-500': errors.stock }"
                placeholder="0"
              />
              <p v-if="errors.stock" class="mt-1 text-sm text-red-600">{{ errors.stock }}</p>
            </div>

            <div>
              <label for="sku" class="block text-sm font-medium text-gray-700 mb-1">
                SKU
              </label>
              <input
                id="sku"
                v-model="form.sku"
                type="text"
                class="input-field"
                :class="{ 'border-red-500': errors.sku }"
                placeholder="Enter SKU"
              />
              <p v-if="errors.sku" class="mt-1 text-sm text-red-600">{{ errors.sku }}</p>
            </div>

            <div>
              <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                Category
              </label>
              <input
                id="category"
                v-model="form.category"
                type="text"
                class="input-field"
                :class="{ 'border-red-500': errors.category }"
                placeholder="Enter category"
              />
              <p v-if="errors.category" class="mt-1 text-sm text-red-600">{{ errors.category }}</p>
            </div>

            <div>
              <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">
                Brand
              </label>
              <input
                id="brand"
                v-model="form.brand"
                type="text"
                class="input-field"
                :class="{ 'border-red-500': errors.brand }"
                placeholder="Enter brand"
              />
              <p v-if="errors.brand" class="mt-1 text-sm text-red-600">{{ errors.brand }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Product Image
              </label>
              <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                <div class="space-y-1 text-center">
                  <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <div class="flex text-sm text-gray-600">
                    <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                      <span>Upload a file</span>
                      <input
                        id="image"
                        ref="imageInput"
                        type="file"
                        accept="image/*"
                        class="sr-only"
                        @change="handleImageChange"
                      />
                    </label>
                    <p class="pl-1">or drag and drop</p>
                  </div>
                  <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                </div>
              </div>
              <div v-if="imagePreview" class="mt-4">
                <img :src="imagePreview" alt="Preview" class="w-32 h-32 object-cover rounded-lg" />
              </div>
              <p v-if="errors.image" class="mt-1 text-sm text-red-600">{{ errors.image }}</p>
            </div>

            <div class="md:col-span-2">
              <div class="flex items-center">
                <input
                  id="is_active"
                  v-model="form.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
                />
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                  Active Product
                </label>
              </div>
              <p class="mt-1 text-sm text-gray-500">Active products are visible to customers</p>
            </div>
          </div>

          <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-sm text-red-600">{{ error }}</p>
          </div>

          <div class="flex justify-end space-x-4">
            <button
              type="button"
              @click="toggleEditMode"
              class="btn-secondary"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary flex items-center"
            >
              <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
              {{ loading ? 'Updating...' : 'Update Product' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Delete Product</h3>
        <p class="text-sm text-gray-500 mb-6">
          Are you sure you want to delete "{{ productsStore.currentProduct?.name }}"? This action cannot be undone.
        </p>
        <div class="flex justify-end space-x-4">
          <button
            @click="showDeleteModal = false"
            class="btn-secondary"
          >
            Cancel
          </button>
          <button
            @click="handleDelete"
            :disabled="loading"
            class="btn-danger flex items-center"
          >
            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
            {{ loading ? 'Deleting...' : 'Delete' }}
          </button>
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
const router = useRouter()
const productsStore = useProductsStore()

const isEditMode = ref(false)
const showDeleteModal = ref(false)
const loading = ref(false)
const error = ref('')
const errors = ref({})
const imageFile = ref(null)
const imagePreview = ref(null)
const imageInput = ref(null)

const form = ref({
  name: '',
  description: '',
  price: null,
  stock: null,
  sku: '',
  category: '',
  brand: '',
  is_active: true
})

// Fetch product on page load
onMounted(async () => {
  const productId = parseInt(route.params.id)
  await productsStore.fetchProduct(productId)
  
  if (productsStore.currentProduct) {
    initializeForm()
  }
})

const initializeForm = () => {
  const product = productsStore.currentProduct
  form.value = {
    name: product.name,
    description: product.description || '',
    price: product.price,
    stock: product.stock,
    sku: product.sku || '',
    category: product.category || '',
    brand: product.brand || '',
    is_active: product.is_active
  }
}

const toggleEditMode = () => {
  isEditMode.value = !isEditMode.value
  if (isEditMode.value) {
    initializeForm()
  } else {
    errors.value = {}
    error.value = ''
    imageFile.value = null
    imagePreview.value = null
  }
}

const validateForm = () => {
  errors.value = {}
  
  if (!form.value.name) {
    errors.value.name = 'Product name is required'
  } else if (form.value.name.length < 2) {
    errors.value.name = 'Product name must be at least 2 characters'
  }
  
  if (form.value.description && form.value.description.length > 1000) {
    errors.value.description = 'Description must be less than 1000 characters'
  }
  
  if (!form.value.price) {
    errors.value.price = 'Price is required'
  } else if (form.value.price < 0) {
    errors.value.price = 'Price must be positive'
  }
  
  if (!form.value.stock) {
    errors.value.stock = 'Stock quantity is required'
  } else if (form.value.stock < 0) {
    errors.value.stock = 'Stock quantity must be positive'
  }
  
  if (form.value.sku && form.value.sku.length > 50) {
    errors.value.sku = 'SKU must be less than 50 characters'
  }
  
  if (form.value.category && form.value.category.length > 100) {
    errors.value.category = 'Category must be less than 100 characters'
  }
  
  if (form.value.brand && form.value.brand.length > 100) {
    errors.value.brand = 'Brand must be less than 100 characters'
  }
  
  return Object.keys(errors.value).length === 0
}

const handleImageChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    if (file.size > 2 * 1024 * 1024) {
      errors.value.image = 'Image size must be less than 2MB'
      return
    }
    
    if (!file.type.startsWith('image/')) {
      errors.value.image = 'Please select a valid image file'
      return
    }
    
    imageFile.value = file
    imagePreview.value = URL.createObjectURL(file)
    errors.value.image = null
  }
}

const handleSubmit = async () => {
  if (!validateForm()) return
  
  loading.value = true
  error.value = ''
  
  try {
    const formData = new FormData()
    
    // Add form fields
    Object.keys(form.value).forEach(key => {
      if (form.value[key] !== null && form.value[key] !== '') {
        formData.append(key, form.value[key])
      }
    })
    
    // Add image if selected
    if (imageFile.value) {
      formData.append('image', imageFile.value)
    }
    
    const result = await productsStore.updateProduct(productsStore.currentProduct.id, formData)
    
    if (result.success) {
      isEditMode.value = false
      errors.value = {}
      error.value = ''
      imageFile.value = null
      imagePreview.value = null
    } else {
      error.value = result.error
    }
  } catch (err) {
    error.value = 'An unexpected error occurred'
  } finally {
    loading.value = false
  }
}

const handleDelete = async () => {
  loading.value = true
  
  try {
    const result = await productsStore.deleteProduct(productsStore.currentProduct.id)
    
    if (result.success) {
      await router.push('/products')
    } else {
      error.value = result.error
      showDeleteModal.value = false
    }
  } catch (err) {
    error.value = 'An unexpected error occurred'
    showDeleteModal.value = false
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>
