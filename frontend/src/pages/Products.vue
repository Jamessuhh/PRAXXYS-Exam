<script setup>
import { onMounted, ref } from 'vue'
import { useProductStore } from '../store/products'
import { storeToRefs } from 'pinia'
import ProductFormModal from '../components/products/ProductFormModal.vue'
import ProductsTable from '../components/products/ProductsTable.vue'

const store = useProductStore()

// Using storeToRefs to maintain reactivity
const { products, categories, searchQuery, selectedCategory, currentPage, totalPages, loading } = storeToRefs(store)
// Default to localhost if env variable is not set
const imageBaseUrl = ref(import.meta.env.VITE_API_URL || 'http://localhost:8000')
// Base64 encoded simple placeholder image (gray background with "No Image" text)
const defaultImageUrl = ref('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2VlZWVlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM5OTk5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=')

const showModal = ref(false)
const productToEdit = ref(null)
const showDeleteModal = ref(false)
const productToDelete = ref(null)

// Watch is no longer needed as it's handled in the store actions

onMounted(() => {
  store.fetchProducts()
  store.fetchCategories()
})

const openCreateModal = () => {
  productToEdit.value = null
  showModal.value = true
}

const openEditModal = (product) => {
  productToEdit.value = product
  showModal.value = true
}

const openDeleteModal = (product) => {
  productToDelete.value = product
  showDeleteModal.value = true
}

const confirmDelete = () => {
  if (productToDelete.value) {
    store.deleteProduct(productToDelete.value.id)
    showDeleteModal.value = false
    productToDelete.value = null
  }
}

const getProductImage = (product) => {
  try {
    if (product.images && product.images.length > 0) {
      const imagePath = product.images[0].path
      // Remove any leading slashes and construct the full URL
      const cleanPath = imagePath.replace(/^\/+/, '')
      const fullUrl = `${imageBaseUrl.value}/storage/${cleanPath}`
      
      // Debug logging
      console.log({
        baseUrl: imageBaseUrl.value,
        imagePath: imagePath,
        fullUrl: fullUrl
      })
      
      return fullUrl
    }
    return defaultImageUrl.value
  } catch (error) {
    console.error('Error getting product image:', error)
    return defaultImageUrl.value
  }
}

// Add this to verify the environment variable is loaded
console.log('Environment check:', {
  VITE_API_URL: import.meta.env.VITE_API_URL,
  imageBaseUrl: imageBaseUrl.value
})
</script>

<template>
  <div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-primary">Products</h1>
      <button @click="openCreateModal" class="btn btn-primary">
        Create Product
      </button>
    </div>

    <!-- Search and Filter Section -->
    <div class="flex flex-col md:flex-row gap-4 mb-8 bg-base-200 p-4 rounded-lg shadow-sm">
      <input
        type="text"
        v-model="searchQuery"
        @input="store.setSearchQuery(searchQuery)"
        placeholder="Search products..."
        class="input input-bordered w-full md:w-1/3 focus:input-primary"
      />
      <select
        v-model="selectedCategory"
        @change="store.setCategory(selectedCategory)"
        class="select select-bordered w-full md:w-1/4 focus:select-primary"
      >
        <option value="">All Categories</option>
        <option v-for="category in categories" :key="category" :value="category">
          {{ category }}
        </option>
      </select>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center my-8">
      <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>

    <!-- Products Table -->
    <template v-else>
      <ProductsTable
        :products="products"
        :default-image-url="defaultImageUrl"
        :image-base-url="imageBaseUrl"
        :on-edit="openEditModal"
        :on-delete="openDeleteModal"
      />

      <!-- Pagination -->
      <div class="flex justify-center my-6">
        <div class="join shadow-sm">
          <button
            class="join-item btn btn-primary"
            :disabled="currentPage === 1"
            @click="store.setPage(currentPage - 1)"
          >
            «
          </button>
          <button class="join-item btn">Page {{ currentPage }}</button>
          <button
            class="join-item btn btn-primary"
            :disabled="currentPage === totalPages"
            @click="store.setPage(currentPage + 1)"
          >
            »
          </button>
        </div>
      </div>
    </template>

    <!-- Add modal component -->
    <ProductFormModal
      :is-open="showModal"
      :product-to-edit="productToEdit"
      @close="showModal = false"
    />

    <!-- Add Delete Confirmation Modal -->
    <dialog :open="showDeleteModal" class="modal modal-bottom sm:modal-middle">
      <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Delete</h3>
        <p class="py-4">
          Are you sure you want to delete "{{ productToDelete?.name }}"? This action cannot be undone.
        </p>
        <div class="modal-action">
          <button @click="showDeleteModal = false" class="btn">Cancel</button>
          <button @click="confirmDelete" class="btn btn-error">Delete</button>
        </div>
      </div>
      <form method="dialog" class="modal-backdrop">
        <button @click="showDeleteModal = false">close</button>
      </form>
    </dialog>
  </div>
</template>

<style scoped>
</style>
