<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 bg-black/50 z-50 transition-opacity duration-300"
    @click="$emit('close')"
  >
    <div
      class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-xl transform transition-transform duration-300 ease-in-out"
      @click.stop
    >
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Shopping Cart</h2>
          <p class="text-sm text-gray-500">
            {{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }}
          </p>
        </div>
        <button
          @click="$emit('close')"
          class="p-2 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-md"
        >
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <div class="flex-1 overflow-y-auto max-h-[calc(100vh-200px)]">
        <div v-if="isLoading" class="flex items-center justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          <span class="ml-2 text-gray-600">Updating cart...</span>
        </div>

        <div v-else-if="isEmpty" class="flex flex-col items-center justify-center py-12 px-6">
          <div class="text-gray-400 mb-4">
            <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1"
                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l-1 12H6L5 9z"
              />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
          <p class="text-gray-500 text-center mb-6">
            Looks like you haven't added anything to your cart yet.
          </p>
          <button
            @click="$emit('close')"
            class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Continue Shopping
          </button>
        </div>

        <div v-else class="p-6 space-y-4">
          <div
            v-for="item in cart.items"
            :key="item.productId"
            class="flex items-start space-x-4 py-4 border-b border-gray-100 last:border-b-0"
          >
            <div class="flex-shrink-0">
              <img
                :src="item.image"
                :alt="item.title"
                class="h-16 w-16 object-cover rounded-md border border-gray-200"
                @error="handleImageError"
              />
            </div>

            <div class="flex-1 min-w-0">
              <h4 class="text-sm font-medium text-gray-900 mb-1 line-clamp-2">
                {{ item.title }}
              </h4>
              <p class="text-sm text-gray-500 mb-2">
                {{ formatPrice(item.price, item.currency) }} each
              </p>

              <div class="flex items-center space-x-3">
                <div class="flex items-center border border-gray-300 rounded-md">
                  <button
                    @click="decrementQuantity(item.productId, item.quantity)"
                    :disabled="isUpdating"
                    class="p-1 text-gray-400 hover:text-gray-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M20 12H4"
                      />
                    </svg>
                  </button>

                  <span
                    class="px-3 py-1 text-sm font-medium text-gray-900 min-w-[3rem] text-center"
                  >
                    {{ item.quantity }}
                  </span>

                  <button
                    @click="incrementQuantity(item.productId, item.quantity)"
                    :disabled="isUpdating"
                    class="p-1 text-gray-400 hover:text-gray-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                      />
                    </svg>
                  </button>
                </div>

                <button
                  @click="handleRemoveItem(item.productId)"
                  :disabled="isUpdating"
                  class="text-red-500 hover:text-red-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                  title="Remove item"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                </button>
              </div>
            </div>

            <div class="flex-shrink-0 text-right">
              <p class="text-sm font-semibold text-gray-900">
                {{ formatPrice(item.totalPrice, item.currency) }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!isEmpty" class="border-t border-gray-200 bg-gray-50 px-6 py-4">
        <div v-if="hasDiscount" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-md">
          <div class="flex items-center">
            <svg class="h-5 w-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd"
              />
            </svg>
            <span class="text-sm font-medium text-green-800">
              🎉 You're saving 10% on your order!
            </span>
          </div>
        </div>

        <div
          v-else-if="subtotal > 150"
          class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md"
        >
          <div class="flex items-center">
            <svg class="h-5 w-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd"
              />
            </svg>
            <span class="text-sm font-medium text-blue-800">
              Add {{ formatPrice(200 - subtotal, currency) }} more for 10% discount!
            </span>
          </div>
        </div>

        <div class="space-y-2 mb-4">
          <div class="flex justify-between text-sm text-gray-600">
            <span>Subtotal</span>
            <span>{{ formatPrice(subtotal, currency) }}</span>
          </div>

          <div v-if="hasDiscount" class="flex justify-between text-sm text-green-600">
            <span>Discount (10%)</span>
            <span>-{{ formatPrice(discount, currency) }}</span>
          </div>

          <div
            class="flex justify-between text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200"
          >
            <span>Total</span>
            <span>{{ formatPrice(total, currency) }}</span>
          </div>
        </div>

        <div class="space-y-3">
          <button
            @click="handleClearCart"
            :disabled="isUpdating"
            class="w-full bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <span v-if="isUpdating">Clearing...</span>
            <span v-else>Clear Cart</span>
          </button>

          <button
            @click="handleCheckout"
            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors duration-200"
          >
            Proceed to Checkout
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useCart } from '@/composables/useCart'
import { formatPrice } from '@/services/api'

interface Props {
  isOpen: boolean
}

defineProps<Props>()

const emit = defineEmits<{
  close: []
}>()

const {
  cart,
  itemCount,
  isEmpty,
  subtotal,
  discount,
  total,
  hasDiscount,
  currency,
  isLoading,
  updateQuantity,
  removeItem,
  clearCart,
} = useCart()

const isUpdating = computed(() => isLoading.value)

const incrementQuantity = async (productId: number, currentQuantity: number) => {
  await updateQuantity(productId, currentQuantity + 1)
}

const decrementQuantity = async (productId: number, currentQuantity: number) => {
  if (currentQuantity > 1) {
    await updateQuantity(productId, currentQuantity - 1)
  } else {
    await removeItem(productId)
  }
}

const handleRemoveItem = async (productId: number) => {
  if (confirm('Remove this item from your cart?')) {
    await removeItem(productId)
  }
}

const handleClearCart = async () => {
  if (confirm('Remove all items from your cart?')) {
    await clearCart()
  }
}

const handleCheckout = () => {
  alert('🎉 Demo checkout! In a real app, this would redirect to payment.')
  emit('close')
}

const handleImageError = (event: Event) => {
  const img = event.target as HTMLImageElement
  img.src = 'https://via.placeholder.com/64x64?text=No+Image'
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom scrollbar for webkit browsers */
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
</style>
