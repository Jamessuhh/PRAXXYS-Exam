import axios from 'axios'
import { defineStore } from 'pinia'
import axiosClient from '../axios.js'

// Add base URL configuration
const backendUrl = import.meta.env.VITE_API_URL
axios.defaults.baseURL = backendUrl

// Add this utility function at the top of the file
async function compressImage(file) {
  return new Promise((resolve) => {
    const reader = new FileReader()
    reader.onload = (event) => {
      const img = new Image()
      img.onload = () => {
        const canvas = document.createElement('canvas')
        const MAX_WIDTH = 1200
        const MAX_HEIGHT = 1200
        let width = img.width
        let height = img.height

        if (width > height) {
          if (width > MAX_WIDTH) {
            height *= MAX_WIDTH / width
            width = MAX_WIDTH
          }
        } else {
          if (height > MAX_HEIGHT) {
            width *= MAX_HEIGHT / height
            height = MAX_HEIGHT
          }
        }

        canvas.width = width
        canvas.height = height
        const ctx = canvas.getContext('2d')
        ctx.drawImage(img, 0, 0, width, height)

        canvas.toBlob((blob) => {
          resolve(new File([blob], file.name, {
            type: 'image/jpeg',
            lastModified: Date.now()
          }))
        }, 'image/jpeg', 0.7) // Compress with 70% quality
      }
      img.src = event.target.result
    }
    reader.readAsDataURL(file)
  })
}

async function validateAndCompressImage(file) {
  // Check file size (2MB = 2 * 1024 * 1024 bytes)
  const maxSize = 2 * 1024 * 1024
  
  if (file.size > maxSize) {
    // If file is too large, compress it
    return await compressImage(file)
  }
  
  return file
}

export const useProductStore = defineStore('products', {
  state: () => ({
    products: [],
    categories: [],
    searchQuery: '',
    selectedCategory: '',
    currentPage: 1,
    totalPages: 0,
    loading: false
  }),

  actions: {
    async fetchProducts() {
      try {
        this.loading = true
        const response = await axiosClient.get('/api/products', {
          params: {
            search: this.searchQuery,
            category: this.selectedCategory,
            page: this.currentPage,
          }
        })
        
        // The products are now in response.data.products
        this.products = response.data.products || []
      } catch (error) {
        console.error('Error fetching products:', error)
      } finally {
        this.loading = false
      }
    },

    async fetchCategories() {
      try {
        const response = await axiosClient.get('/api/categories')
        this.categories = response.data.map(category => category.name)
      } catch (error) {
        console.error('Error fetching categories:', error)
      }
    },

    async deleteProduct(id) {
      try {
        const response = await axiosClient.delete(`/api/products/${id}`)
        return response.data
      } catch (error) {
        console.error('Error deleting product:', error)
        throw error
      }
    },

    setSearchQuery(query) {
      this.searchQuery = query
      this.currentPage = 1
      this.fetchProducts()
    },

    setCategory(category) {
      this.selectedCategory = category
      this.currentPage = 1
      this.fetchProducts()
    },

    setPage(page) {
      this.currentPage = page
      this.fetchProducts()
    },

    async createProduct(product) {
      try {
        this.loading = true
        const formData = new FormData()
        
        // Add basic product data with validation
        if (!product.name) throw new Error('Name is required')
        if (!product.category) throw new Error('Category is required')
        if (!product.description) throw new Error('Description is required')
        if (!product.datetime) throw new Error('Date and time is required')
        
        formData.append('name', product.name)
        formData.append('category', product.category)
        formData.append('description', product.description)
        formData.append('datetime', product.datetime)
        
        // Handle new images with compression
        if (product.new_images && product.new_images.length) {
          const compressedImages = await Promise.all(
            product.new_images.map(image => validateAndCompressImage(image))
          )
          
          compressedImages.forEach((image, index) => {
            formData.append(`images[${index}]`, image)
          })
        }

        // For debugging - log the FormData contents
        for (let pair of formData.entries()) {
          console.log('FormData:', pair[0] + ': ' + pair[1])
        }

        const response = await axiosClient.post('/api/products', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        // Log the response for debugging
        console.log('Create product response:', response.data)

        this.products.unshift(response.data)
        return response.data
      } catch (error) {
        console.error('Error creating product:', error)
        // Rethrow the error with a more user-friendly message
        if (error.message) {
          throw new Error(error.message)
        } else if (error.response?.data?.message) {
          throw new Error(error.response.data.message)
        } else {
          throw new Error('Failed to create product')
        }
      } finally {
        this.loading = false
      }
    },

    async updateProduct(product) {
      try {
        this.loading = true
        const formData = new FormData()
        
        // Append basic product info
        formData.append('name', product.name)
        formData.append('category', product.category)
        formData.append('description', product.description)
        formData.append('datetime', product.datetime)
        formData.append('_method', 'PUT')
        
        // Handle existing images
        if (product.existing_images && product.existing_images.length) {
          product.existing_images.forEach((path, index) => {
            const cleanPath = path.replace(/^products\//, '')
            formData.append(`existing_images[${index}]`, cleanPath)
          })
        }
        
        // Handle new images with compression
        if (product.new_images && product.new_images.length) {
          const compressedImages = await Promise.all(
            product.new_images.map(image => validateAndCompressImage(image))
          )
          
          compressedImages.forEach((image, index) => {
            formData.append(`images[${index}]`, image)
          })
        }

        // For debugging - log the FormData contents
        for (let pair of formData.entries()) {
          console.log(pair[0] + ': ' + pair[1])
        }

        const response = await axiosClient.post(`/api/products/${product.id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })

        // Update the product in the local state
        const updatedProduct = response.data
        const index = this.products.findIndex(p => p.id === updatedProduct.id)
        if (index !== -1) {
          this.products[index] = updatedProduct
        }

        return response.data
      } catch (error) {
        console.error('Error updating product:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    async getProduct(id) {
      try {
        this.loading = true
        console.log('Fetching product with ID:', id)
        console.log('Using base URL:', axios.defaults.baseURL)
        
        const response = await axios.get(`/api/products/${id}`)
        console.log('Product response:', response.data)
        return response.data
      } catch (error) {
        console.error('Error fetching product:', error)
        // Log more detailed error information
        if (error.response) {
          console.error('Response data:', error.response.data)
          console.error('Response status:', error.response.status)
          console.error('Response headers:', error.response.headers)
        }
        throw error
      } finally {
        this.loading = false
      }
    },

    // Update the edit product action to fetch fresh data first
    async editProduct(id) {
      try {
        const product = await this.getProduct(id)
        return product
      } catch (error) {
        console.error('Error getting product for edit:', error)
        throw error
      }
    }
  }
}) 