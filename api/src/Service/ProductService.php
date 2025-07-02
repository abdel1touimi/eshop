<?php

namespace App\Service;

use App\DTO\ProductDTO;

class ProductService
{
  public function __construct(
    private readonly FakeStoreApiService $fakeStoreApiService
  ) {}

  public function getAllProducts(
    ?int $limit = null,
    ?string $sort = null,
    ?string $category = null,
    ?float $minPrice = null,
    ?float $maxPrice = null
  ): array {
    $productsData = $this->fakeStoreApiService->getAllProducts($limit, $sort);

    $products = array_map(
      fn(array $productData) => ProductDTO::fromFakeStoreApi($productData),
      $productsData
    );

    $filteredProducts = $this->applyFilters($products, $category, $minPrice, $maxPrice);

    return $filteredProducts;
  }

  public function getProduct(int $id): ProductDTO
  {
    $productData = $this->fakeStoreApiService->getProduct($id);
    return ProductDTO::fromFakeStoreApi($productData);
  }

  public function getProductsByCategory(
    string $category,
    ?int $limit = null,
    ?string $sort = null,
    ?float $minPrice = null,
    ?float $maxPrice = null
  ): array {
    $productsData = $this->fakeStoreApiService->getProductsByCategory($category, $limit, $sort);

    $products = array_map(
      fn(array $productData) => ProductDTO::fromFakeStoreApi($productData),
      $productsData
    );

    $filteredProducts = $this->applyFilters($products, null, $minPrice, $maxPrice);

    return $filteredProducts;
  }

  public function getCategories(): array
  {
    return $this->fakeStoreApiService->getCategories();
  }

  public function searchProducts(
    string $query,
    ?int $limit = null,
    ?string $sort = null,
    ?string $category = null,
    ?float $minPrice = null,
    ?float $maxPrice = null
  ): array {
    $products = $this->getAllProducts($limit, $sort, $category, $minPrice, $maxPrice);

    $searchTerm = strtolower(trim($query));

    $filtered = array_filter($products, function (ProductDTO $product) use ($searchTerm) {
      return str_contains(strtolower($product->title), $searchTerm) ||
             str_contains(strtolower($product->description), $searchTerm) ||
             str_contains(strtolower($product->category), $searchTerm);
    });

    return array_values($filtered);
  }

  private function applyFilters(
    array $products,
    ?string $category = null,
    ?float $minPrice = null,
    ?float $maxPrice = null
  ): array {
    $filtered = array_filter($products, function (ProductDTO $product) use ($category, $minPrice, $maxPrice) {
      if ($category && strcasecmp($product->category, $category) !== 0) {
        return false;
      }

      if ($minPrice !== null && $product->price < $minPrice) {
        return false;
      }

      if ($maxPrice !== null && $product->price > $maxPrice) {
        return false;
      }

      return true;
    });

    return array_values($filtered);
  }

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
