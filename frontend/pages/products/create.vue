<template>
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Create Product</h1>
          <p class="mt-2 text-gray-600">Add a new product to your inventory</p>
        </div>
        <NuxtLink to="/products" class="btn-secondary">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Back to Products
        </NuxtLink>
      </div>
    </div>

    <form @submit.prevent="handleSubmit" class="card">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="md:col-span-2">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
        </div>

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

      <div v-if="error" class="mt-6 bg-red-50 border border-red-200 rounded-md p-4">
        <p class="text-sm text-red-600">{{ error }}</p>
      </div>

      <div class="mt-8 flex justify-end space-x-4">
        <NuxtLink to="/products" class="btn-secondary">
          Cancel
        </NuxtLink>
        <button
          type="submit"
          :disabled="loading"
          class="btn-primary flex items-center"
        >
          <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
          {{ loading ? 'Creating...' : 'Create Product' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: ['auth', 'product']
})

const productsStore = useProductsStore()
const router = useRouter()

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

const errors = ref({})
const error = ref('')
const loading = ref(false)
const imageFile = ref(null)
const imagePreview = ref(null)
const imageInput = ref(null)

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
    // Validate file size (2MB limit)
    if (file.size > 2 * 1024 * 1024) {
      errors.value.image = 'Image size must be less than 2MB'
      return
    }

    // Validate file type
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
    console.log(formData)
    const result = await productsStore.createProduct(formData)

    if (result.success) {
      await router.push('/products')
    } else {
      error.value = result.error
    }
  } catch (err) {
    console.log("error", err)
    error.value = 'An unexpected error occurred'
  } finally {
    loading.value = false
  }
}
</script>
