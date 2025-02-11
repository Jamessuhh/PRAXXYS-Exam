<script setup>
import { ref, watch, onMounted } from 'vue'
import { useProductStore } from '../../store/products'

const props = defineProps({
  isOpen: Boolean,
  productToEdit: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['close'])
const store = useProductStore()
const currentStep = ref(1)
const loading = ref(false)

// Add backend URL from environment variable
const backendUrl = import.meta.env.VITE_API_URL // Make sure this is set in your .env file

const form = ref({
  name: '',
  category: '',
  description: '',
  images: [],
  datetime: '',
  existing_images: [],
  new_images: []
})

const errors = ref({})

// Reset form when modal opens/closes
watch(() => props.isOpen, (newVal) => {
  if (newVal) {
    currentStep.value = 1
    if (props.productToEdit) {
      form.value = {
        name: props.productToEdit.name || '',
        category: props.productToEdit.category || '',
        description: props.productToEdit.description || '',
        images: props.productToEdit.images ? 
          props.productToEdit.images.map(img => img.path) : 
          [],
        datetime: props.productToEdit.datetime || '',
        existing_images: props.productToEdit.images?.map(img => img.path) || [],
        new_images: []
      }
    } else {
      form.value = {
        name: '',
        category: '',
        description: '',
        images: [],
        datetime: '',
        existing_images: [],
        new_images: []
      }
    }
  }
})

const validateStep1 = () => {
  errors.value = {}
  if (!form.value.name) errors.value.name = 'Name is required'
  if (!form.value.category) errors.value.category = 'Category is required'
  if (!form.value.description) errors.value.description = 'Description is required'
  return Object.keys(errors.value).length === 0
}

const validateStep2 = () => {
  errors.value = {}
  if (form.value.images.length === 0) {
    errors.value.images = 'At least one image is required'
  }
  return Object.keys(errors.value).length === 0
}

const validateStep3 = () => {
  errors.value = {}
  if (!form.value.datetime) errors.value.datetime = 'Date and time is required'
  return Object.keys(errors.value).length === 0
}

const nextStep = () => {
  const isValid = currentStep.value === 1 
    ? validateStep1() 
    : currentStep.value === 2 
    ? validateStep2()
    : true

  if (isValid) {
    currentStep.value++
  }
}

const prevStep = () => {
  currentStep.value--
}

const handleImageUpload = (event) => {
  const files = Array.from(event.target.files || [])
  if (files.length > 0) {
    // Store the actual File objects in new_images
    form.value.new_images = [...form.value.new_images, ...files]
    
    // Add the file names to images array for display/validation
    form.value.images = [
      ...form.value.existing_images,
      ...files.map(file => file.name)
    ]
  }
}

const removeImage = (index) => {
  // Remove from both arrays
  form.value.images = form.value.images.filter((_, i) => i !== index)
  if (index >= form.value.existing_images.length) {
    // If it's a new image
    const newIndex = index - form.value.existing_images.length
    form.value.new_images = form.value.new_images.filter((_, i) => i !== newIndex)
  } else {
    // If it's an existing image
    form.value.existing_images = form.value.existing_images.filter((_, i) => i !== index)
  }
}

const getImagePreviewUrl = (image) => {
  if (image instanceof File) {
    return URL.createObjectURL(image)
  }
  // If image is a path string from the API
  if (typeof image === 'string') {
    try {
      // Handle both full URLs and relative paths
      if (image.startsWith('http')) {
        return image
      }
      return `${backendUrl}/storage/${image}`
    } catch (error) {
      console.error('Error creating image URL:', error)
      return '' // Return empty string or a placeholder image URL
    }
  }
  return ''
}

const handleSubmit = async () => {
  try {
    // Validate all steps before submitting
    if (!validateStep1()) return
    if (!validateStep2()) return
    if (!validateStep3()) return

    const productData = {
      name: form.value.name,
      category: form.value.category,
      description: form.value.description,
      datetime: form.value.datetime,
      new_images: form.value.new_images
    }

    console.log('Submitting product data:', productData)

    if (props.productToEdit) {
      await store.updateProduct({
        id: props.productToEdit.id,
        ...productData
      })
    } else {
      await store.createProduct(productData)
    }
    
    emit('close')
  } catch (error) {
    console.error('Submit error:', error)
    // You might want to show this error to the user
    alert(error.message)
  }
}
</script>

<template>
  <dialog :class="{ 'modal': true, 'modal-open': isOpen }">
    <div class="modal-box max-w-4xl p-6 bg-base-100">
      <!-- Header with improved styling -->
      <h3 class="font-bold text-2xl mb-6 text-primary">
        {{ productToEdit ? 'Edit Product' : 'Create New Product' }}
      </h3>

      <!-- Enhanced step indicator -->
      <ul class="steps steps-horizontal w-full mb-8">
        <li 
          v-for="(step, index) in ['Details', 'Images', 'Schedule']" 
          :key="index"
          class="step font-medium" 
          :class="{ 
            'step-primary': currentStep >= index + 1,
            'text-primary': currentStep === index + 1
          }"
        >
          {{ step }}
        </li>
      </ul>

      <!-- Step 1: Basic Details -->
      <div v-if="currentStep === 1" class="space-y-6">
        <div class="form-control">
          <label class="label">
            <span class="label-text text-base font-medium text-gray-700">Name</span>
          </label>
          <input 
            type="text" 
            v-model="form.name"
            class="input input-bordered bg-white w-full px-4 py-3 rounded-xl border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200"
            :class="{ 'input-error': errors.name }"
            placeholder="Enter product name"
          />
          <label v-if="errors.name" class="label">
            <span class="label-text-alt text-error">{{ errors.name }}</span>
          </label>
        </div>

        <div class="form-control">
          <label class="label">
            <span class="label-text text-base font-medium text-gray-700">Category</span>
          </label>
          <div class="relative">
            <select 
              v-model="form.category"
              class="select select-bordered appearance-none bg-white w-full h-[3rem] leading-[3rem] px-4 rounded-xl border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200"
              :class="{ 'select-error': errors.category }"
            >
              <option value="" disabled selected>Select a category</option>
              <option v-for="category in store.categories" :key="category" :value="category" class="py-2">
                {{ category }}
              </option>
            </select>
            <!-- Custom dropdown arrow -->
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
          <label v-if="errors.category" class="label">
            <span class="label-text-alt text-error">{{ errors.category }}</span>
          </label>
        </div>

        <div class="form-control">
          <label class="label">
            <span class="label-text text-base font-medium text-gray-700">Description</span>
          </label>
          <textarea
            v-model="form.description"
            class="textarea textarea-bordered bg-white w-full px-4 py-3 rounded-xl border-gray-200 focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all duration-200 min-h-[160px]"
            :class="{ 'textarea-error': errors.description }"
            placeholder="Enter detailed product description..."
          ></textarea>
          <label v-if="errors.description" class="label">
            <span class="label-text-alt text-error">{{ errors.description }}</span>
          </label>
        </div>
      </div>

      <!-- Step 2: Images -->
      <div v-if="currentStep === 2" class="space-y-6">
        <div class="form-control">
          <label class="label">
            <span class="label-text font-medium">Product Images</span>
            <span class="label-text-alt text-base-content/70">Upload multiple images</span>
          </label>
          <div class="border-2 border-dashed border-base-300 rounded-lg p-8 text-center hover:border-primary transition-colors">
            <input 
              type="file"
              @change="handleImageUpload"
              multiple
              accept="image/*"
              class="file-input file-input-bordered w-full max-w-md"
              :class="{ 'file-input-error': errors.images }"
            />
            <p class="mt-2 text-sm text-base-content/70">
              Drag and drop files here or click to browse
            </p>
          </div>
          <label v-if="errors.images" class="label">
            <span class="label-text-alt text-error">{{ errors.images }}</span>
          </label>
        </div>

        <!-- Enhanced image preview -->
        <div v-if="form.images.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="(image, index) in form.images" :key="index" 
               class="relative group rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
            <img 
              :src="getImagePreviewUrl(image)"
              class="w-full h-40 object-cover"
              alt="Product image"
            />
            <button 
              @click="removeImage(index)" 
              class="btn btn-circle btn-sm absolute top-2 right-2 bg-error hover:bg-error-focus text-white opacity-0 group-hover:opacity-100 transition-opacity"
            >
              ×
            </button>
          </div>
        </div>
      </div>

      <!-- Step 3: Date and Time -->
      <div v-if="currentStep === 3" class="space-y-6">
        <div class="form-control">
          <label class="label">
            <span class="label-text font-medium">Date and Time</span>
          </label>
          <input 
            type="datetime-local"
            v-model="form.datetime"
            class="input input-bordered input-lg shadow-sm focus:input-primary transition-colors"
            :class="{ 'input-error': errors.datetime }"
          />
          <label v-if="errors.datetime" class="label">
            <span class="label-text-alt text-error">{{ errors.datetime }}</span>
          </label>
        </div>
      </div>

      <!-- Enhanced navigation buttons -->
      <div class="modal-action gap-2 mt-8">
        <button 
          v-if="currentStep > 1" 
          @click="prevStep"
          class="btn btn-outline btn-lg"
        >
          ← Previous
        </button>
        <button 
          v-if="currentStep < 3" 
          @click="nextStep"
          class="btn btn-primary btn-lg"
        >
          Next →
        </button>
        <button 
          v-if="currentStep === 3"
          @click="handleSubmit"
          class="btn btn-primary btn-lg"
          :disabled="loading"
        >
          <span v-if="loading" class="loading loading-spinner loading-sm mr-2"></span>
          {{ loading ? 'Saving...' : 'Save Product' }}
        </button>
        <button @click="emit('close')" class="btn btn-ghost btn-lg">Cancel</button>
      </div>
    </div>
  </dialog>
</template> 