<?php

namespace App\Controller;

use App\Service\ProductService;
use App\DTO\Response\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/products')]
class ProductController extends AbstractController
{
  public function __construct(
    private readonly ProductService $productService,
    private readonly LoggerInterface $logger
  ) {}

  /**
   * Get all products with filtering, sorting, and pagination
   *
   * Query parameters:
   * - limit: int (e.g., 10)
   * - sort: string (asc|desc)
   * - category: string
   * - min_price: float
   * - max_price: float
   * - search: string
   */
  #[Route('', methods: ['GET'])]
  public function getAllProducts(Request $request): JsonResponse
  {
    try {
      $this->logger->info('Fetching products with filters', $request->query->all());

      $limit = $request->query->get('limit') ? (int) $request->query->get('limit') : null;
      $sort = $request->query->get('sort');
      $category = $request->query->get('category');
      $minPrice = $request->query->get('min_price') ? (float) $request->query->get('min_price') : null;
      $maxPrice = $request->query->get('max_price') ? (float) $request->query->get('max_price') : null;
      $search = $request->query->get('search');

      if ($sort && !in_array(strtolower($sort), ['asc', 'desc'])) {
        $sort = null;
      }

      if ($search) {
        $products = $this->productService->searchProducts($search, $limit, $sort, $category, $minPrice, $maxPrice);
      } else {
        $products = $this->productService->getAllProducts($limit, $sort, $category, $minPrice, $maxPrice);
      }

      $this->logger->info('Products fetched successfully', [
        'count' => count($products),
        'filters' => compact('limit', 'sort', 'category', 'minPrice', 'maxPrice', 'search')
      ]);

      $responseData = [
        'products' => array_map(fn($product) => $product->toArray(), $products),
        'meta' => [
          'total' => count($products),
          'limit' => $limit,
          'sort' => $sort,
          'filters' => [
            'category' => $category,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'search' => $search
          ]
        ]
      ];

      $response = ApiResponse::success(
        data: $responseData,
        message: 'Products retrieved successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to fetch products', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Please try again later',
        statusCode: 500,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/{id}', methods: ['GET'], requirements: ['id' => '\d+'])]
  public function getProduct(int $id): JsonResponse
  {
    try {
      $this->logger->info('Fetching product', ['productId' => $id]);

      $product = $this->productService->getProduct($id);

      $this->logger->info('Product fetched successfully', [
        'productId' => $id,
        'title' => $product->title
      ]);

      $response = ApiResponse::success(
        data: $product->toArray(),
        message: 'Product retrieved successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to fetch product', [
        'productId' => $id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Product not found',
        statusCode: 404,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/category/{category}', methods: ['GET'])]
  public function getProductsByCategory(string $category, Request $request): JsonResponse
  {
    try {
      $this->logger->info('Fetching products by category', ['category' => $category]);

      $limit = $request->query->get('limit') ? (int) $request->query->get('limit') : null;
      $sort = $request->query->get('sort');
      $minPrice = $request->query->get('min_price') ? (float) $request->query->get('min_price') : null;
      $maxPrice = $request->query->get('max_price') ? (float) $request->query->get('max_price') : null;

      if ($sort && !in_array(strtolower($sort), ['asc', 'desc'])) {
        $sort = null;
      }

      $products = $this->productService->getProductsByCategory($category, $limit, $sort, $minPrice, $maxPrice);

      $this->logger->info('Products fetched successfully', [
        'category' => $category,
        'count' => count($products)
      ]);

      $responseData = [
        'products' => array_map(fn($product) => $product->toArray(), $products),
        'meta' => [
          'total' => count($products),
          'category' => $category,
          'limit' => $limit,
          'sort' => $sort,
          'filters' => [
            'min_price' => $minPrice,
            'max_price' => $maxPrice
          ]
        ]
      ];

      $response = ApiResponse::success(
        data: $responseData,
        message: 'Products retrieved successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to fetch products by category', [
        'category' => $category,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Please try again later',
        statusCode: 500,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/categories', methods: ['GET'])]
  public function getCategories(): JsonResponse
  {
    try {
      $this->logger->info('Fetching all product categories');
      $categories = $this->productService->getCategories();

      $this->logger->info('Categories fetched successfully', [
        'count' => count($categories)
      ]);

      $response = ApiResponse::success(
        data: $categories,
        message: 'Categories retrieved successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to fetch categories', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Please try again later',
        statusCode: 500,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/stats', methods: ['GET'])]
  public function getProductStats(): JsonResponse
  {
    try {
      $this->logger->info('Fetching product statistics');
      $stats = $this->productService->getProductStats();

      $this->logger->info('Product statistics fetched successfully', $stats);

      $response = ApiResponse::success(
        data: $stats,
        message: 'Product statistics retrieved successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to fetch product statistics', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Please try again later',
        statusCode: 500,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }
}
