<template>
  <div
    v-if="isOpen && product"
    class="fixed inset-0 bg-black/50 z-50 transition-opacity duration-300"
    @click="$emit('close')"
  >
    <div
      class="fixed inset-0 overflow-y-auto"
      @click.stop
    >
      <div class="flex items-center justify-center min-h-screen p-4">
        <div
          class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-xl transform transition-all duration-300"
          @click.stop
        >
          <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900 pr-8">Product Details</h2>
            <button
              @click="$emit('close')"
              class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-6">
              <div class="space-y-4">
                <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                  <img
                    :src="product.image"
                    :alt="product.title"
                    class="w-full h-full object-contain hover:scale-105 transition-transform duration-300"
                    @error="handleImageError"
                  >
                </div>

                <div class="flex space-x-2">
                  <button
                    @click="openImageInNewTab"
                    class="flex-1 px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  >
                    <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    View Full Size
                  </button>
                </div>
              </div>

              <div class="space-y-6">
                <div>
                  <div class="flex items-center space-x-2 mb-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                      {{ product.category }}
                    </span>
                  </div>
                  <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ product.title }}
                  </h1>
                </div>

                <div v-if="product.rating" class="flex items-center space-x-2">
                  <div class="flex items-center">
                    <div class="flex space-x-1">
                      <svg
                        v-for="star in 5"
                        :key="star"
                        class="h-5 w-5"
                        :class="star <= Math.round(product.rating.rate) ? 'text-yellow-400' : 'text-gray-300'"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                      </svg>
                    </div>
                    <span class="ml-2 text-sm font-medium text-gray-900">
                      {{ product.rating.rate }}/5
                    </span>
                  </div>
                  <span class="text-sm text-gray-500">
                    ({{ product.rating.count }} {{ product.rating.count === 1 ? 'review' : 'reviews' }})
                  </span>
                </div>

                <div class="border-t border-b border-gray-200 py-4">
                  <div class="flex items-baseline space-x-2">
                    <span class="text-3xl font-bold text-gray-900">
                      {{ formatPrice(product.price, product.currency) }}
                    </span>
                    <span class="text-sm text-gray-500">{{ product.currency }}</span>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">Price includes all applicable taxes</p>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Description</h3>
                  <div class="prose prose-sm text-gray-700 max-w-none">
                    <p class="leading-relaxed">{{ product.description }}</p>
                  </div>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-3">Product Details</h3>
                  <dl class="grid grid-cols-1 gap-3">
                    <div class="flex justify-between py-2 border-b border-gray-100">
                      <dt class="text-sm font-medium text-gray-500">Product ID</dt>
                      <dd class="text-sm text-gray-900">#{{ product.id }}</dd>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                      <dt class="text-sm font-medium text-gray-500">Category</dt>
                      <dd class="text-sm text-gray-900 capitalize">{{ product.category }}</dd>
                    </div>
                    <div v-if="product.rating" class="flex justify-between py-2 border-b border-gray-100">
                      <dt class="text-sm font-medium text-gray-500">Average Rating</dt>
                      <dd class="text-sm text-gray-900">{{ product.rating.rate }}/5 ({{ product.rating.count }} reviews)</dd>
                    </div>
                    <div class="flex justify-between py-2">
                      <dt class="text-sm font-medium text-gray-500">Availability</dt>
                      <dd class="text-sm text-green-600 font-medium">In Stock</dd>
                    </div>
                  </dl>
                </div>

                <div class="space-y-4 pt-4">
                  <div class="flex items-center space-x-4">
                    <label for="quantity" class="text-sm font-medium text-gray-700">Quantity:</label>
                    <div class="flex items-center border border-gray-300 rounded-md">
                      <button
                        @click="decrementQuantity"
                        :disabled="selectedQuantity <= 1"
                        class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                        </svg>
                      </button>

                      <span class="px-4 py-2 text-sm font-medium text-gray-900 min-w-[3rem] text-center">
                        {{ selectedQuantity }}
                      </span>

                      <button
                        @click="incrementQuantity"
                        :disabled="selectedQuantity >= 10"
                        class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                      </button>
                    </div>
                  </div>

                  <div v-if="isInCart(product.id)" class="p-3 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex items-center">
                      <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      <span class="text-sm font-medium text-green-800">
                        Already in cart ({{ getProductQuantity(product.id) }} {{ getProductQuantity(product.id) === 1 ? 'item' : 'items' }})
                      </span>
                    </div>
                  </div>

                  <button
                    @click="handleAddToCart"
                    :disabled="isLoading"
                    class="w-full bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 font-medium"
                  >
                    <span v-if="isLoading" class="flex items-center justify-center">
                      <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Adding to Cart...
                    </span>
                    <span v-else class="flex items-center justify-center">
                      <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.8 9H19M7 13v6a2 2 0 002 2h6a2 2 0 002-2v-6" />
                      </svg>
                      Add {{ selectedQuantity }} to Cart
                    </span>
                  </button>

                  <div class="flex space-x-4 pt-2">
                    <button
                      @click="shareProduct"
                      class="flex-1 px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <svg class="h-4 w-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                      </svg>
                      Share
                    </button>

                    <button
                      @click="toggleWishlist"
                      class="px-4 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      :class="{ 'text-red-600 border-red-300': isInWishlist }"
                    >
                      <svg class="h-4 w-4 inline mr-2" :class="{ 'fill-current': isInWishlist }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                      {{ isInWishlist ? 'Saved' : 'Save' }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useCart } from '@/composables/useCart'
import { formatPrice } from '@/services/api'
import type { Product } from '@/types/api'

interface Props {
  isOpen: boolean
  product: Product | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
  close: []
  productAdded: [productId: number, quantity: number]
}>()

const { addToCart, isInCart, getProductQuantity, isLoading } = useCart()

const selectedQuantity = ref(1)
const isInWishlist = ref(false)

const incrementQuantity = () => {
  if (selectedQuantity.value < 10) {
    selectedQuantity.value++
  }
}

const decrementQuantity = () => {
  if (selectedQuantity.value > 1) {
    selectedQuantity.value--
  }
}

const handleAddToCart = async () => {
  if (!props.product) return

  const result = await addToCart(props.product.id, selectedQuantity.value)

  if (result.success) {
    emit('productAdded', props.product.id, selectedQuantity.value)
    selectedQuantity.value = 1
  }
}

const openImageInNewTab = () => {
  if (props.product?.image) {
    window.open(props.product.image, '_blank')
  }
}

const shareProduct = () => {
  if (!props.product) return

  if (navigator.share) {
    navigator.share({
      title: props.product.title,
      text: props.product.description,
      url: window.location.href
    })
  } else {
    const shareText = `Check out this product: ${props.product.title} - ${window.location.href}`
    navigator.clipboard.writeText(shareText).then(() => {
      alert('Product link copied to clipboard!')
    }).catch(() => {
      alert('Unable to share. Please copy the URL manually.')
    })
  }
}

const toggleWishlist = () => {
  isInWishlist.value = !isInWishlist.value
  const action = isInWishlist.value ? 'added to' : 'removed from'
  console.log(`Product ${action} wishlist (demo)`)
}

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.src = 'https://via.placeholder.com/500x500?text=No+Image+Available'
}

computed(() => {
  if (props.isOpen && props.product) {
    selectedQuantity.value = 1
  }
})
</script>

<style scoped>
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

.aspect-square img {
  transition: transform 0.3s ease;
}

.prose {
  line-height: 1.6;
}
</style>
