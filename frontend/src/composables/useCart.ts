import { ref, computed, readonly } from 'vue'
import { cartApi } from '@/services/api'
import type { Cart, CartItem } from '@/types/api'

const cart = ref<Cart>({
  items: [],
  totals: {
    subtotal: 0,
    discount: 0,
    total: 0,
    hasDiscount: false,
    currency: 'EUR',
  },
})

const loading = ref(false)
const error = ref('')

export function useCart() {

  const fetchCart = async () => {
    try {
      loading.value = true
      error.value = ''

      const data = await cartApi.get()
      cart.value = data

      console.log(`✅ Fetched cart with ${data.items.length} items`)
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch cart'
      console.error('❌ Failed to fetch cart:', err)
    } finally {
      loading.value = false
    }
  }

  const addToCart = async (productId: number, quantity: number = 1) => {
    try {
      loading.value = true
      error.value = ''

      const data = await cartApi.add(productId, quantity)
      cart.value = data

      console.log(`✅ Added product ${productId} to cart (qty: ${quantity})`)
      return { success: true, message: 'Product added to cart!' }
    } catch (err: any) {
      const message = err.message || 'Failed to add product to cart'
      error.value = message
      console.error('❌ Failed to add to cart:', err)
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const updateQuantity = async (productId: number, quantity: number) => {
    try {
      loading.value = true
      error.value = ''

      const data = await cartApi.update(productId, quantity)
      cart.value = data

      console.log(`✅ Updated product ${productId} quantity to ${quantity}`)
      return { success: true, message: 'Cart updated!' }
    } catch (err: any) {
      const message = err.message || 'Failed to update cart item'
      error.value = message
      console.error('❌ Failed to update quantity:', err)
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const removeItem = async (productId: number) => {
    try {
      loading.value = true
      error.value = ''

      const data = await cartApi.remove(productId)
      cart.value = data

      console.log(`✅ Removed product ${productId} from cart`)
      return { success: true, message: 'Item removed from cart!' }
    } catch (err: any) {
      const message = err.message || 'Failed to remove item from cart'
      error.value = message
      console.error('❌ Failed to remove item:', err)
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const clearCart = async () => {
    try {
      loading.value = true
      error.value = ''

      const data = await cartApi.clear()
      cart.value = data

      console.log('✅ Cart cleared')
      return { success: true, message: 'Cart cleared!' }
    } catch (err: any) {
      const message = err.message || 'Failed to clear cart'
      error.value = message
      console.error('❌ Failed to clear cart:', err)
      return { success: false, message }
    } finally {
      loading.value = false
    }
  }

  const isInCart = (productId: number): boolean => {
    return cart.value.items.some((item) => item.productId === productId)
  }

  const getCartItem = (productId: number): CartItem | undefined => {
    return cart.value.items.find((item) => item.productId === productId)
  }

  const getProductQuantity = (productId: number): number => {
    const item = getCartItem(productId)
    return item ? item.quantity : 0
  }

  const itemCount = computed(() =>
    cart.value.items.reduce((total, item) => total + item.quantity, 0),
  )

  const isEmpty = computed(() => cart.value.items.length === 0)

  const hasItems = computed(() => cart.value.items.length > 0)

  const subtotal = computed(() => cart.value.totals.subtotal)

  const discount = computed(() => cart.value.totals.discount)

  const total = computed(() => cart.value.totals.total)

  const hasDiscount = computed(() => cart.value.totals.hasDiscount)

  const currency = computed(() => cart.value.totals.currency)

  const isLoading = computed(() => loading.value)

  const hasError = computed(() => !!error.value)

  const initialize = async () => {
    await fetchCart()
  }

  return {
    cart: readonly(cart),
    loading: readonly(loading),
    error: readonly(error),

    // Computed
    itemCount,
    isEmpty,
    hasItems,
    subtotal,
    discount,
    total,
    hasDiscount,
    currency,
    isLoading,
    hasError,

    // Methods
    fetchCart,
    addToCart,
    updateQuantity,
    removeItem,
    clearCart,
    isInCart,
    getCartItem,
    getProductQuantity,
    initialize,
  }
}
