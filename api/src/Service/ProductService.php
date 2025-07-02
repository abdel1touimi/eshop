<?php

namespace App\Service;

use App\DTO\ProductDTO;

class ProductService
{
  public function __construct(
    private readonly FakeStoreApiService $fakeStoreApiService
  ) {}

  /**
   * Get all products with filtering, sorting, and pagination
   */
  public function getAllProducts(
    ?int $limit = null,
    ?string $sort = null,
    ?string $category = null,
    ?float $minPrice = null,
    ?float $maxPrice = null
  ): array {
    $productsData = $this->fakeStoreApiService->getAllProducts($limit, $sort);

    // Convert to DTOs
    $products = array_map(
      fn(array $productData) => ProductDTO::fromFakeStoreApi($productData),
      $productsData
    );

    // Apply backend filtering (since Fake Store API doesn't support price filtering)
    $filteredProducts = $this->applyFilters($products, $category, $minPrice, $maxPrice);

    return $filteredProducts;
  }

  /**
   * Get single product by ID with currency transformation
   */
  public function getProduct(int $id): ProductDTO
  {
    $productData = $this->fakeStoreApiService->getProduct($id);
    return ProductDTO::fromFakeStoreApi($productData);
  }

  /**
   * Get products by category with sorting and pagination
   */
  public function getProductsByCategory(
    string $category,
    ?int $limit = null,
    ?string $sort = null,
    ?float $minPrice = null,
    ?float $maxPrice = null
  ): array {
    $productsData = $this->fakeStoreApiService->getProductsByCategory($category, $limit, $sort);

    // Convert to DTOs
    $products = array_map(
      fn(array $productData) => ProductDTO::fromFakeStoreApi($productData),
      $productsData
    );

    // Apply price filtering
    $filteredProducts = $this->applyFilters($products, null, $minPrice, $maxPrice);

    return $filteredProducts;
  }

  /**
   * Get all available categories
   */
  public function getCategories(): array
  {
    return $this->fakeStoreApiService->getCategories();
  }

  /**
   * Search products by title/description (backend filtering)
   */
  public function searchProducts(
    string $query,
    ?int $limit = null,
    ?string $sort = null,
    ?string $category = null,
    ?float $minPrice = null,
    ?float $maxPrice = null
  ): array {
    // Get all products first
    $products = $this->getAllProducts($limit, $sort, $category, $minPrice, $maxPrice);

    // Filter by search query
    $searchTerm = strtolower(trim($query));

    $filtered = array_filter($products, function (ProductDTO $product) use ($searchTerm) {
      return str_contains(strtolower($product->title), $searchTerm) ||
             str_contains(strtolower($product->description), $searchTerm) ||
             str_contains(strtolower($product->category), $searchTerm);
    });

    // Re-index array to ensure proper JSON array structure
    return array_values($filtered);
  }

  /**
   * Apply backend filters that Fake Store API doesn't support
   */
  private function applyFilters(
    array $products,
    ?string $category = null,
    ?float $minPrice = null,
    ?float $maxPrice = null
  ): array {
    $filtered = array_filter($products, function (ProductDTO $product) use ($category, $minPrice, $maxPrice) {
      // Category filter (if not already filtered by API)
      if ($category && strcasecmp($product->category, $category) !== 0) {
        return false;
      }

      // Price range filter
      if ($minPrice !== null && $product->price < $minPrice) {
        return false;
      }

      if ($maxPrice !== null && $product->price > $maxPrice) {
        return false;
      }

      return true;
    });

    // Re-index array to ensure it's a proper array, not an object
    return array_values($filtered);
  }

  /**
   * Get product statistics for filters
   */
  public function getProductStats(): array
  {
    $products = $this->getAllProducts();

    $prices = array_map(fn(ProductDTO $product) => $product->price, $products);

    return [
      'total_products' => count($products),
      'min_price' => !empty($prices) ? min($prices) : 0,
      'max_price' => !empty($prices) ? max($prices) : 1000,
      'avg_price' => !empty($prices) ? array_sum($prices) / count($prices) : 0
    ];
  }
}
