<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  products: {
    type: Array,
    required: true
  },
  defaultImageUrl: {
    type: String,
    required: true
  },
  imageBaseUrl: {
    type: String,
    required: true
  },
  onEdit: {
    type: Function,
    required: true
  },
  onDelete: {
    type: Function,
    required: true
  }
})

const getProductImage = (product) => {
  try {
    if (product.images && product.images.length > 0) {
      const imagePath = product.images[0].path
      const cleanPath = imagePath.replace(/^\/+/, '')
      const fullUrl = `${props.imageBaseUrl}/storage/${cleanPath}`
      return fullUrl
    }
    return props.defaultImageUrl
  } catch (error) {
    console.error('Error getting product image:', error)
    return props.defaultImageUrl
  }
}
</script>

<template>
  <div class="bg-base-100 rounded-lg shadow-lg">
    <div class="overflow-x-auto">
      <table class="table table-zebra w-full">
        <thead>
          <tr class="bg-base-200">
            <th class="text-base">Name</th>
            <th class="text-base">Image</th>
            <th class="text-base">Category</th>
            <th class="text-base">Description</th>
            <th class="text-base">Date</th>
            <th class="text-base">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in products" :key="product.id" class="hover:bg-base-200 transition-colors">
            <td class="font-medium">{{ product.name }}</td>
            <td>
              <img 
                :src="getProductImage(product)"
                :alt="product.name"
                class="w-16 h-16 object-cover rounded bg-gray-100"
                @error="(e) => {
                  console.error('Image load error:', e);
                  e.target.src = defaultImageUrl;
                }"
              />
            </td>
            <td><span class="badge badge-primary">{{ product.category }}</span></td>
            <td class="max-w-md truncate">{{ product.description }}</td>
            <td>{{ new Date(product.created_at).toLocaleDateString() }}</td>
            <td>
              <div class="flex gap-2">
                <button
                  @click="onEdit(product)"
                  class="btn btn-info btn-sm"
                >
                  Edit
                </button>
                <button
                  @click="onDelete(product)"
                  class="btn btn-error btn-sm"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template> 