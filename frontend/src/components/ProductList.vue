<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">E-Shop Gallery</h1>
            <p class="text-sm text-gray-500">Powered by Fake Store API</p>
          </div>

          <CartIcon @click="showCart = !showCart" ref="cartIconRef" />
        </div>
      </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <ProductFilters />

      <div v-if="!isLoading && hasProducts" class="mb-6">
        <div class="flex items-center justify-between">
          <p class="text-gray-600">
            Showing {{ productCount }} of {{ totalProducts }} products
          </p>
          <div class="text-sm text-gray-500">
            {{ isLoading ? 'Loading...' : 'Updated' }}
          </div>
        </div>
      </div>

      <div v-if="isLoading" class="flex items-center justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        <span class="ml-3 text-lg text-gray-600">Loading products...</span>
      </div>

      <div v-else-if="hasError" class="bg-red-50 border border-red-200 rounded-md p-6 text-center">
        <div class="text-red-400 mx-auto h-12 w-12 mb-4">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-red-800 mb-2">Error Loading Products</h3>
        <p class="text-red-700 mb-4">{{ error }}</p>
        <button
          @click="initialize"
          class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
        >
          Try Again
        </button>
      </div>

      <div v-else-if="hasProducts">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="product in products"
            :key="product.id"
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200"
          >
            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-200">
              <img
                :src="product.image"
                :alt="product.title"
                class="h-48 w-full object-cover object-center group-hover:opacity-75"
                @error="handleImageError"
              >
            </div>

            <div class="p-4">
              <div class="mb-2">
                <h3
                  @click="openProductDetails(product)"
                  class="text-sm font-medium text-gray-900 line-clamp-2 mb-1 cursor-pointer hover:text-blue-600 transition-colors"
                >
                  {{ product.title }}
                </h3>
                <p class="text-xs text-gray-500 capitalize">{{ product.category }}</p>
              </div>

              <p class="text-sm text-gray-600 line-clamp-2 mb-3">
                {{ product.description }}
              </p>

              <div class="flex items-center justify-between mb-3">
                <span class="text-lg font-bold text-blue-600">
                  {{ formatPrice(product.price, product.currency) }}
                </span>
                <div v-if="product.rating" class="flex items-center text-sm text-gray-500">
                  <svg class="h-4 w-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                  </svg>
                  {{ product.rating.rate }}
                </div>
              </div>

              <div class="flex space-x-2">
                <button
                  @click="handleAddToCart(product.id)"
                  :disabled="cartLoading"
                  class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200 text-sm"
                >
                  <span v-if="cartLoading" class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Adding...
                  </span>
                  <span v-else-if="isInCart(product.id)" class="flex items-center justify-center">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    In Cart ({{ getProductQuantity(product.id) }})
                  </span>
                  <span v-else>Add to Cart</span>
                </button>

                <button
                  @click="openProductDetails(product)"
                  class="px-3 py-2 text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
                  title="View Details"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <div class="text-gray-400 mx-auto h-16 w-16 mb-4">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2M4 13h2" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
        <p class="text-gray-500 mb-4">
          Try adjusting your search or category filter.
        </p>
        <button
          @click="clearAllFilters"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Clear All Filters
        </button>
      </div>
    </div>

    <CartModal :is-open="showCart" @close="showCart = false" />
    <ProductDetailsModal
      :is-open="showProductDetails"
      :product="selectedProduct"
      @close="closeProductDetails"
      @product-added="onProductAdded"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useProducts } from '@/composables/useProducts'
import { useCart } from '@/composables/useCart'
import { formatPrice } from '@/services/api'
import type { Product } from '@/types/api'
import CartModal from '@/components/CartModal.vue'
import CartIcon from '@/components/CartIcon.vue'
import ProductFilters from '@/components/ProductFilters.vue'
import ProductDetailsModal from '@/components/ProductDetailsModal.vue'

const {
  products,
  productCount,
  totalProducts,
  hasProducts,
  isLoading,
  hasError,
  error,
  initialize: initializeProducts
} = useProducts()

const {
  loading: cartLoading,
  addToCart,
  isInCart,
  getProductQuantity,
  initialize: initializeCart
} = useCart()

const showCart = ref(false)
const showProductDetails = ref(false)
const selectedProduct = ref<Product | null>(null)
const cartIconRef = ref()

const handleAddToCart = async (productId: number) => {
  const result = await addToCart(productId, 1)

  if (result.success) {
    if (cartIconRef.value) {
      cartIconRef.value.triggerAnimation()
    }
    console.log('✅ Product added to cart')
  } else {
    console.error('❌ Failed to add product to cart')
  }
}

const openProductDetails = (product: Product) => {
  selectedProduct.value = product
  showProductDetails.value = true
}

const closeProductDetails = () => {
  showProductDetails.value = false
  selectedProduct.value = null
}

const onProductAdded = (productId: number, quantity: number) => {
  if (cartIconRef.value) {
    cartIconRef.value.triggerAnimation()
  }
  console.log(`✅ ${quantity} item(s) added to cart from product details`)
}

const clearAllFilters = () => {
  console.log('Clear all filters triggered')
}

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.src = 'https://via.placeholder.com/400x400?text=No+Image'
}

const initialize = async () => {
  await Promise.all([
    initializeProducts(),
    initializeCart()
  ])
}

onMounted(() => {
  initialize()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.aspect-w-1 {
  position: relative;
  padding-bottom: 100%;
}

.aspect-w-1 > * {
  position: absolute;
  height: 100%;
  width: 100%;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}
</style>
