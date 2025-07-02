export interface Product {
  id: number
  title: string
  price: number
  currency: string
  description: string
  category: string
  image: string
  rating?: {
    rate: number
    count: number
  }
}

export interface CartItem {
  productId: number
  title: string
  price: number
  currency: string
  quantity: number
  image: string
  totalPrice: number
}

export interface CartTotals {
  subtotal: number
  discount: number
  total: number
  hasDiscount: boolean
  currency: string
}

export interface Cart {
  items: CartItem[]
  totals: CartTotals
}

export interface ApiResponse<T> {
  success: boolean
  message: string
  data?: T
  error?: string
  errors?: ValidationError[]
}

export interface ValidationError {
  field: string
  message: string
  value: any
}

export interface AddToCartRequest {
  productId: number
  quantity: number
}

export interface UpdateCartItemRequest {
  quantity: number
}
