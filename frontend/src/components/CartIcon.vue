<template>
  <button
    @click="$emit('click')"
    class="relative p-3 text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg transition-colors duration-200 group"
    :class="{ 'animate-bounce': isAnimating }"
  >
    <svg
      class="h-6 w-6 transition-transform duration-200 group-hover:scale-110"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.8 9H19M7 13v6a2 2 0 002 2h6a2 2 0 002-2v-6"
      />
    </svg>

    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="scale-0 opacity-0"
      enter-to-class="scale-100 opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="scale-100 opacity-100"
      leave-to-class="scale-0 opacity-0"
    >
      <span
        v-if="itemCount > 0"
        class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full h-6 w-6 flex items-center justify-center border-2 border-white shadow-lg"
        :class="{ 'animate-pulse': isAnimating }"
      >
        {{ displayCount }}
      </span>
    </Transition>

    <div v-if="isLoading" class="absolute -top-1 -right-1 h-6 w-6 flex items-center justify-center">
      <div
        class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent"
      ></div>
    </div>

    <div
      v-if="showTooltip && itemCount > 0"
      class="absolute right-0 top-full mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 p-4 z-50 transform transition-all duration-200"
      :class="
        tooltipVisible
          ? 'opacity-100 translate-y-0'
          : 'opacity-0 -translate-y-2 pointer-events-none'
      "
    >
      <div
        class="absolute -top-2 right-4 w-4 h-4 bg-white border-l border-t border-gray-200 transform rotate-45"
      ></div>

      <div class="space-y-2">
        <h4 class="font-semibold text-gray-900 text-sm mb-2">Cart Summary</h4>

        <div class="space-y-1 max-h-32 overflow-y-auto">
          <div
            v-for="item in cart.items.slice(0, 3)"
            :key="item.productId"
            class="flex items-center justify-between text-xs"
          >
            <span class="truncate flex-1 mr-2 text-gray-600">{{ item.title }}</span>
            <span class="text-gray-900 font-medium">{{ item.quantity }}x</span>
          </div>
          <div v-if="cart.items.length > 3" class="text-xs text-gray-500 text-center py-1">
            +{{ cart.items.length - 3 }} more items
          </div>
        </div>

        <div class="border-t border-gray-200 pt-2 mt-2">
          <div class="flex justify-between items-center">
            <span class="text-sm font-semibold text-gray-900">Total:</span>
            <span class="text-sm font-bold text-blue-600">{{ formatPrice(total, currency) }}</span>
          </div>
          <div v-if="hasDiscount" class="text-xs text-green-600 text-right">
            10% discount applied!
          </div>
        </div>

        <button
          class="w-full bg-blue-600 text-white text-xs py-2 rounded-md hover:bg-blue-700 transition-colors duration-200"
        >
          View Cart
        </button>
      </div>
    </div>
  </button>
</template>

<script setup lang="ts">
import { computed, ref, watch, nextTick } from 'vue'
import { useCart } from '@/composables/useCart'
import { formatPrice } from '@/services/api'

defineEmits<{
  click: []
}>()

const { cart, itemCount, total, currency, hasDiscount, isLoading } = useCart()

const isAnimating = ref(false)
const showTooltip = ref(false)
const tooltipVisible = ref(false)
let tooltipTimer: number | null = null

const displayCount = computed(() => {
  return itemCount.value > 99 ? '99+' : itemCount.value.toString()
})

watch(itemCount, (newCount, oldCount) => {
  if (newCount > oldCount) {
    triggerAnimation()
  }
})

const triggerAnimation = async () => {
  isAnimating.value = true
  await nextTick()
  setTimeout(() => {
    isAnimating.value = false
  }, 600)
}

defineExpose({
  triggerAnimation,
})
</script>

<style scoped>
@keyframes bounce-subtle {
  0%,
  20%,
  50%,
  80%,
  100% {
    transform: translateY(0);
  }
  40% {
    transform: translateY(-4px);
  }
  60% {
    transform: translateY(-2px);
  }
}

.animate-bounce {
  animation: bounce-subtle 0.6s ease-in-out;
}

.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
