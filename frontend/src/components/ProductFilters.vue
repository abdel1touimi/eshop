<template>
  <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
      <button
        @click="clearAllFilters"
        class="text-sm text-blue-600 hover:text-blue-800 focus:outline-none"
      >
        Clear All
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="lg:col-span-2">
        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
          Search Products
        </label>
        <div class="relative">
          <input
            id="search"
            v-model="localFilters.search"
            @input="debouncedSearch"
            type="text"
            placeholder="Search products..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div>
        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
          Category
        </label>
        <select
          id="category"
          v-model="localFilters.category"
          @change="applyFilters"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">All Categories</option>
          <option v-for="category in categories" :key="category" :value="category">
            {{ formatCategoryName(category) }}
          </option>
        </select>
      </div>

      <div>
        <label for="sort" class="block text-sm font-medium text-gray-700 mb-2">
          Sort By
        </label>
        <select
          id="sort"
          v-model="localFilters.sort"
          @change="applyFilters"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Default</option>
          <option value="asc">Price: Low to High</option>
          <option value="desc">Price: High to Low</option>
        </select>
      </div>

      <div>
        <label for="limit" class="block text-sm font-medium text-gray-700 mb-2">
          Show
        </label>
        <select
          id="limit"
          v-model="localFilters.limit"
          @change="applyFilters"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        >
          <option :value="undefined">All Products</option>
          <option :value="5">5 Products</option>
          <option :value="10">10 Products</option>
          <option :value="15">15 Products</option>
          <option :value="20">20 Products</option>
        </select>
      </div>
    </div>

    <div class="mt-6" v-if="priceRange.min < priceRange.max">
      <label class="block text-sm font-medium text-gray-700 mb-2">
        Price Range: {{ formatPrice(currentMinPrice, 'EUR') }} - {{ formatPrice(currentMaxPrice, 'EUR') }}
      </label>

      <div class="grid grid-cols-2 gap-4 mt-2">
        <div>
          <input
            v-model.number="localFilters.minPrice"
            @input="debouncedPriceChange"
            type="range"
            :min="priceRange.min"
            :max="priceRange.max"
            :step="1"
            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
          >
          <div class="text-xs text-gray-500 mt-1">Min: {{ formatPrice(localFilters.minPrice || priceRange.min, 'EUR') }}</div>
        </div>

        <div>
          <input
            v-model.number="localFilters.maxPrice"
            @input="debouncedPriceChange"
            type="range"
            :min="priceRange.min"
            :max="priceRange.max"
            :step="1"
            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider"
          >
          <div class="text-xs text-gray-500 mt-1">Max: {{ formatPrice(localFilters.maxPrice || priceRange.max, 'EUR') }}</div>
        </div>
      </div>
    </div>

    <div v-if="activeFiltersCount > 0" class="mt-4 pt-4 border-t border-gray-200">
      <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-700">Active Filters ({{ activeFiltersCount }})</span>
      </div>

      <div class="flex flex-wrap gap-2">
        <span
          v-if="localFilters.search"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
        >
          Search: "{{ localFilters.search }}"
          <button @click="clearFilter('search')" class="ml-2 text-blue-600 hover:text-blue-800">×</button>
        </span>

        <span
          v-if="localFilters.category"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800"
        >
          {{ formatCategoryName(localFilters.category) }}
          <button @click="clearFilter('category')" class="ml-2 text-green-600 hover:text-green-800">×</button>
        </span>

        <span
          v-if="localFilters.sort"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800"
        >
          {{ localFilters.sort === 'asc' ? 'Price: Low to High' : 'Price: High to Low' }}
          <button @click="clearFilter('sort')" class="ml-2 text-purple-600 hover:text-purple-800">×</button>
        </span>

        <span
          v-if="localFilters.limit"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
        >
          Limit: {{ localFilters.limit }}
          <button @click="clearFilter('limit')" class="ml-2 text-yellow-600 hover:text-yellow-800">×</button>
        </span>

        <span
          v-if="hasPriceFilter"
          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"
        >
          Price: {{ formatPrice(currentMinPrice, 'EUR') }} - {{ formatPrice(currentMaxPrice, 'EUR') }}
          <button @click="clearPriceFilter" class="ml-2 text-red-600 hover:text-red-800">×</button>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useProducts } from '@/composables/useProducts'
import { formatPrice, type ProductFilters } from '@/services/api'

const debounce = (func: Function, wait: number) => {
  let timeout: number
  return (...args: any[]) => {
    clearTimeout(timeout)
    timeout = window.setTimeout(() => func.apply(null, args), wait)
  }
}

const { categories, priceRange, applyFilters: applyProductFilters, clearFilters } = useProducts()

const localFilters = ref<ProductFilters>({
  search: '',
  category: '',
  sort: undefined,
  limit: undefined,
  minPrice: undefined,
  maxPrice: undefined
})

const currentMinPrice = computed(() => localFilters.value.minPrice || priceRange.value.min)
const currentMaxPrice = computed(() => localFilters.value.maxPrice || priceRange.value.max)

const activeFiltersCount = computed(() => {
  let count = 0
  if (localFilters.value.search) count++
  if (localFilters.value.category) count++
  if (localFilters.value.sort) count++
  if (localFilters.value.limit) count++
  if (hasPriceFilter.value) count++
  return count
})

const hasPriceFilter = computed(() => {
  return (localFilters.value.minPrice !== undefined && localFilters.value.minPrice > priceRange.value.min) ||
         (localFilters.value.maxPrice !== undefined && localFilters.value.maxPrice < priceRange.value.max)
})

const debouncedSearch = debounce(() => {
  applyFilters()
}, 500)

const debouncedPriceChange = debounce(() => {
  applyFilters()
}, 800)

const applyFilters = async () => {
  const cleanFilters: ProductFilters = {}

  if (localFilters.value.search?.trim()) {
    cleanFilters.search = localFilters.value.search.trim()
  }

  if (localFilters.value.category) {
    cleanFilters.category = localFilters.value.category
  }

  if (localFilters.value.sort) {
    cleanFilters.sort = localFilters.value.sort
  }

  if (localFilters.value.limit) {
    cleanFilters.limit = localFilters.value.limit
  }

  if (localFilters.value.minPrice !== undefined && localFilters.value.minPrice > priceRange.value.min) {
    cleanFilters.minPrice = localFilters.value.minPrice
  }

  if (localFilters.value.maxPrice !== undefined && localFilters.value.maxPrice < priceRange.value.max) {
    cleanFilters.maxPrice = localFilters.value.maxPrice
  }

  await applyProductFilters(cleanFilters)
}

const clearFilter = async (filterKey: keyof ProductFilters) => {
  localFilters.value[filterKey] = undefined
  await applyFilters()
}

const clearPriceFilter = async () => {
  localFilters.value.minPrice = undefined
  localFilters.value.maxPrice = undefined
  await applyFilters()
}

const clearAllFilters = async () => {
  localFilters.value = {
    search: '',
    category: '',
    sort: undefined,
    limit: undefined,
    minPrice: undefined,
    maxPrice: undefined
  }
  await clearFilters()
}

const formatCategoryName = (category: string) => {
  return category.charAt(0).toUpperCase() + category.slice(1).replace(/['"]/g, '')
}

onMounted(() => {
  if (priceRange.value.min < priceRange.value.max) {
    localFilters.value.minPrice = priceRange.value.min
    localFilters.value.maxPrice = priceRange.value.max
  }
})

watch(priceRange, (newRange) => {
  if (localFilters.value.minPrice === undefined) {
    localFilters.value.minPrice = newRange.min
  }
  if (localFilters.value.maxPrice === undefined) {
    localFilters.value.maxPrice = newRange.max
  }
}, { immediate: true })
</script>

<style scoped>
.slider::-webkit-slider-thumb {
  appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.slider::-moz-range-thumb {
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: 2px solid #ffffff;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
</style>
