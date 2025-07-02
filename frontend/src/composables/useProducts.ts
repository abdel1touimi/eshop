import { ref, computed, readonly } from 'vue'
import { productApi, type ProductFilters, type ProductStats } from '@/services/api'
import type { Product } from '@/types/api'

// Global state for products (shared across components)
const products = ref<Product[]>([])
const categories = ref<string[]>([])
const productStats = ref<ProductStats | null>(null)
const loading = ref(false)
const error = ref('')
const currentFilters = ref<ProductFilters>({})
const totalProducts = ref(0)

export function useProducts() {
  const fetchProducts = async (filters: ProductFilters = {}) => {
    try {
      loading.value = true
      error.value = ''
      currentFilters.value = filters

      const response = await productApi.getAll(filters)
      products.value = response.products
      totalProducts.value = response.meta.total

      console.log(`✅ Fetched ${response.products.length} products with filters:`, filters)
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch products'
      console.error('❌ Failed to fetch products:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchCategories = async () => {
    try {
      const data = await productApi.getCategories()
      categories.value = ['All', ...data] // Add "All" option

      console.log(`✅ Fetched ${data.length} categories`)
    } catch (err: any) {
      console.error('❌ Failed to fetch categories:', err)
    }
  }

  const fetchProductStats = async () => {
    try {
      const stats = await productApi.getStats()
      productStats.value = stats

      console.log('✅ Fetched product statistics:', stats)
    } catch (err: any) {
      console.error('❌ Failed to fetch product statistics:', err)
    }
  }

  const fetchProductsByCategory = async (category: string, filters: Omit<ProductFilters, 'category'> = {}) => {
    try {
      loading.value = true
      error.value = ''

      const allFilters = { ...filters, category: category === 'All' ? undefined : category }
      currentFilters.value = allFilters

      if (category === 'All' || category === '') {
        const response = await productApi.getAll(allFilters)
        products.value = response.products
        totalProducts.value = response.meta.total
      } else {
        const response = await productApi.getByCategory(category, filters)
        products.value = response.products
        totalProducts.value = response.meta.total
      }

      console.log(`✅ Fetched ${products.value.length} products for category: ${category}`)
    } catch (err: any) {
      error.value = err.message || 'Failed to fetch products by category'
      console.error('❌ Failed to fetch products by category:', err)
    } finally {
      loading.value = false
    }
  }

  const searchProducts = async (query: string, filters: Omit<ProductFilters, 'search'> = {}) => {
    const searchFilters = { ...filters, search: query }
    await fetchProducts(searchFilters)
  }

  const applyFilters = async (filters: ProductFilters) => {
    await fetchProducts(filters)
  }

  const clearFilters = async () => {
    currentFilters.value = {}
    await fetchProducts()
  }

  const getProductById = async (id: number) => {
    try {
      return await productApi.getById(id)
    } catch (err: any) {
      console.error('❌ Failed to fetch product by ID:', err)
      throw err
    }
  }

  const initialize = async () => {
    await Promise.all([
      fetchProducts(),
      fetchCategories(),
      fetchProductStats()
    ])
  }

  const productCount = computed(() => products.value.length)
  const hasProducts = computed(() => products.value.length > 0)
  const isLoading = computed(() => loading.value)
  const hasError = computed(() => !!error.value)
  const priceRange = computed(() => ({
    min: productStats.value?.min_price || 0,
    max: productStats.value?.max_price || 1000
  }))

  return {
    products: readonly(products),
    categories: readonly(categories),
    productStats: readonly(productStats),
    currentFilters: readonly(currentFilters),
    totalProducts: readonly(totalProducts),
    loading: readonly(loading),
    error: readonly(error),

    productCount,
    hasProducts,
    isLoading,
    hasError,
    priceRange,

    fetchProducts,
    fetchCategories,
    fetchProductStats,
    fetchProductsByCategory,
    searchProducts,
    applyFilters,
    clearFilters,
    getProductById,
    initialize
  }
}
