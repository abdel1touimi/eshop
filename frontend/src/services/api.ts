import axios from 'axios'
import type { Product, Cart, ApiResponse, AddToCartRequest, UpdateCartItemRequest } from '@/types/api'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL, // Hardcoded as working
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json'
  }
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error)

    // Handle specific HTTP errors
    if (error.response?.status === 404) {
      throw new Error('Resource not found')
    } else if (error.response?.status >= 500) {
      throw new Error('Server error. Please try again later.')
    } else if (error.response?.data?.message) {
      throw new Error(error.response.data.message)
    } else {
      throw new Error('Network error. Please check your connection.')
    }
  }
)

export interface ProductFilters {
  limit?: number
  sort?: 'asc' | 'desc'
  category?: string
  minPrice?: number
  maxPrice?: number
  search?: string
}

export interface ProductResponse {
  products: Product[]
  meta: {
    total: number
    limit?: number
    sort?: string
    filters: {
      category?: string
      min_price?: number
      max_price?: number
      search?: string
    }
  }
}

export interface ProductStats {
  total_products: number
  min_price: number
  max_price: number
  avg_price: number
}

export const productApi = {
  getAll: async (filters: ProductFilters = {}): Promise<ProductResponse> => {
    const params = new URLSearchParams()

    if (filters.limit) params.append('limit', filters.limit.toString())
    if (filters.sort) params.append('sort', filters.sort)
    if (filters.category) params.append('category', filters.category)
    if (filters.minPrice !== undefined) params.append('min_price', filters.minPrice.toString())
    if (filters.maxPrice !== undefined) params.append('max_price', filters.maxPrice.toString())
    if (filters.search) params.append('search', filters.search)

    const queryString = params.toString()
    const url = queryString ? `/products?${queryString}` : '/products'

    const response = await api.get<ApiResponse<ProductResponse>>(url)
    if (response.data.success && response.data.data) {
      const data = response.data.data

      let products = data.products
      if (!Array.isArray(products) && typeof products === 'object') {
        products = Object.values(products)
      }

      return {
        products: products as Product[],
        meta: data.meta
      }
    }
    throw new Error(response.data.message || 'Failed to fetch products')
  },

  getById: async (id: number): Promise<Product> => {
    const response = await api.get<ApiResponse<Product>>(`/products/${id}`)
    if (response.data.success && response.data.data) {
      return response.data.data
    }
    throw new Error(response.data.message || 'Failed to fetch product')
  },

  getByCategory: async (category: string, filters: Omit<ProductFilters, 'category'> = {}): Promise<ProductResponse> => {
    const params = new URLSearchParams()

    if (filters.limit) params.append('limit', filters.limit.toString())
    if (filters.sort) params.append('sort', filters.sort)
    if (filters.minPrice !== undefined) params.append('min_price', filters.minPrice.toString())
    if (filters.maxPrice !== undefined) params.append('max_price', filters.maxPrice.toString())

    const queryString = params.toString()
    const url = queryString
      ? `/products/category/${encodeURIComponent(category)}?${queryString}`
      : `/products/category/${encodeURIComponent(category)}`

    const response = await api.get<ApiResponse<ProductResponse>>(url)
    if (response.data.success && response.data.data) {
      const data = response.data.data

      let products = data.products
      if (!Array.isArray(products) && typeof products === 'object') {
        products = Object.values(products)
      }

      return {
        products: products as Product[],
        meta: data.meta
      }
    }
    throw new Error(response.data.message || 'Failed to fetch products by category')
  },

  getCategories: async (): Promise<string[]> => {
    const response = await api.get<ApiResponse<string[]>>('/products/categories')
    if (response.data.success && response.data.data) {
      return response.data.data
    }
    throw new Error(response.data.message || 'Failed to fetch categories')
  },

  getStats: async (): Promise<ProductStats> => {
    const response = await api.get<ApiResponse<ProductStats>>('/products/stats')
    if (response.data.success && response.data.data) {
      return response.data.data
    }
    throw new Error(response.data.message || 'Failed to fetch product statistics')
  }
}

export const cartApi = {
  get: async (): Promise<Cart> => {
    const response = await api.get<ApiResponse<Cart>>('/cart')
    if (response.data.success && response.data.data) {
      return response.data.data
    }
    throw new Error(response.data.message || 'Failed to fetch cart')
  },

  add: async (productId: number, quantity: number = 1): Promise<Cart> => {
    const payload: AddToCartRequest = { productId, quantity }
    const response = await api.post<ApiResponse<Cart>>('/cart/add', payload)

    if (response.data.success && response.data.data) {
      return response.data.data
    }

    if (response.data.errors) {
      const errorMessages = response.data.errors.map(error => error.message).join(', ')
      throw new Error(errorMessages)
    }

    throw new Error(response.data.message || 'Failed to add product to cart')
  },

  update: async (productId: number, quantity: number): Promise<Cart> => {
    const payload: UpdateCartItemRequest = { quantity }
    const response = await api.put<ApiResponse<Cart>>(`/cart/item/${productId}`, payload)

    if (response.data.success && response.data.data) {
      return response.data.data
    }

    if (response.data.errors) {
      const errorMessages = response.data.errors.map(error => error.message).join(', ')
      throw new Error(errorMessages)
    }

    throw new Error(response.data.message || 'Failed to update cart item')
  },

  remove: async (productId: number): Promise<Cart> => {
    const response = await api.delete<ApiResponse<Cart>>(`/cart/item/${productId}`)
    if (response.data.success && response.data.data) {
      return response.data.data
    }
    throw new Error(response.data.message || 'Failed to remove product from cart')
  },

  clear: async (): Promise<Cart> => {
    const response = await api.delete<ApiResponse<Cart>>('/cart/clear')
    if (response.data.success && response.data.data) {
      return response.data.data
    }
    throw new Error(response.data.message || 'Failed to clear cart')
  },

  getCount: async (): Promise<number> => {
    const response = await api.get<ApiResponse<{ count: number }>>('/cart/count')
    if (response.data.success && response.data.data) {
      return response.data.data.count
    }
    throw new Error(response.data.message || 'Failed to get cart count')
  }
}

export const formatPrice = (price: number, currency: string = 'EUR'): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: currency
  }).format(price)
}

export default api
