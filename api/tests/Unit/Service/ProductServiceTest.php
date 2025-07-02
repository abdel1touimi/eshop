<?php

namespace App\Tests\Unit\Service;

use App\Service\ProductService;
use App\Service\FakeStoreApiService;
use App\DTO\ProductDTO;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ProductServiceTest extends TestCase
{
  private ProductService $productService;
  private FakeStoreApiService|MockObject $fakeStoreApiService;

  protected function setUp(): void
  {
    $this->fakeStoreApiService = $this->createMock(FakeStoreApiService::class);
    $this->productService = new ProductService($this->fakeStoreApiService);
  }

  private function createMockApiData(int $id, string $title, float $price, string $category): array
  {
    return [
      'id' => $id,
      'title' => $title,
      'price' => $price,
      'description' => "Description for {$title}",
      'category' => $category,
      'image' => "https://example.com/image{$id}.jpg",
      'rating' => ['rate' => 4.0, 'count' => 100]
    ];
  }

  public function testGetAllProductsBasic(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'Product 1', 50.0, 'electronics'),
      $this->createMockApiData(2, 'Product 2', 75.0, 'clothing')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act
    $products = $this->productService->getAllProducts();

    // Assert
    $this->assertCount(2, $products);
    $this->assertContainsOnlyInstancesOf(ProductDTO::class, $products);
    $this->assertEquals(1, $products[0]->id);
    $this->assertEquals('Product 1', $products[0]->title);
    $this->assertEquals(50.0, $products[0]->price);
    $this->assertEquals('EUR', $products[0]->currency);
  }

  public function testGetAllProductsWithLimitAndSort(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'Product 1', 100.0, 'electronics')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(5, 'desc')
      ->willReturn($mockApiData);

    // Act
    $products = $this->productService->getAllProducts(5, 'desc');

    // Assert
    $this->assertCount(1, $products);
    $this->assertInstanceOf(ProductDTO::class, $products[0]);
  }

  public function testGetAllProductsWithPriceFilter(): void
  {
    // Arrange: Mix of products with different prices
    $mockApiData = [
      $this->createMockApiData(1, 'Cheap Product', 25.0, 'electronics'),
      $this->createMockApiData(2, 'Medium Product', 75.0, 'electronics'),
      $this->createMockApiData(3, 'Expensive Product', 150.0, 'electronics')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act: Filter for products between €50-€100
    $products = $this->productService->getAllProducts(
      limit: null,
      sort: null,
      category: null,
      minPrice: 50.0,
      maxPrice: 100.0
    );

    // Assert: Only medium-priced product should match
    $this->assertCount(1, $products);
    $this->assertEquals('Medium Product', $products[0]->title);
    $this->assertEquals(75.0, $products[0]->price);
  }

  public function testGetAllProductsWithCategoryFilter(): void
  {
    // Arrange: Mix of categories
    $mockApiData = [
      $this->createMockApiData(1, 'Electronics Item', 100.0, 'electronics'),
      $this->createMockApiData(2, 'Clothing Item', 50.0, 'clothing'),
      $this->createMockApiData(3, 'Another Electronics', 75.0, 'electronics')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act: Filter by electronics category
    $products = $this->productService->getAllProducts(
      category: 'electronics'
    );

    // Assert: Only electronics should match
    $this->assertCount(2, $products);
    foreach ($products as $product) {
      $this->assertEquals('electronics', $product->category);
    }
  }

  public function testGetAllProductsWithComplexFilters(): void
  {
    // Arrange: Various products
    $mockApiData = [
      $this->createMockApiData(1, 'Cheap Electronics', 30.0, 'electronics'),
      $this->createMockApiData(2, 'Expensive Electronics', 200.0, 'electronics'),
      $this->createMockApiData(3, 'Mid-range Electronics', 80.0, 'electronics'),
      $this->createMockApiData(4, 'Mid-range Clothing', 80.0, 'clothing')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(10, 'asc')
      ->willReturn($mockApiData);

    // Act: Complex filter - electronics, €50-€150, limit 10, sort asc
    $products = $this->productService->getAllProducts(
      limit: 10,
      sort: 'asc',
      category: 'electronics',
      minPrice: 50.0,
      maxPrice: 150.0
    );

    // Assert: Only mid-range electronics should match
    $this->assertCount(1, $products);
    $this->assertEquals('Mid-range Electronics', $products[0]->title);
    $this->assertEquals(80.0, $products[0]->price);
    $this->assertEquals('electronics', $products[0]->category);
  }

  public function testGetProduct(): void
  {
    // Arrange
    $mockApiData = $this->createMockApiData(5, 'Single Product', 99.99, 'books');

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getProduct')
      ->with(5)
      ->willReturn($mockApiData);

    // Act
    $product = $this->productService->getProduct(5);

    // Assert
    $this->assertInstanceOf(ProductDTO::class, $product);
    $this->assertEquals(5, $product->id);
    $this->assertEquals('Single Product', $product->title);
    $this->assertEquals(99.99, $product->price);
    $this->assertEquals('EUR', $product->currency);
  }

  public function testGetProductsByCategory(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'Jewelry 1', 150.0, 'jewelery'),
      $this->createMockApiData(2, 'Jewelry 2', 200.0, 'jewelery')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getProductsByCategory')
      ->with('jewelery', 5, 'desc')
      ->willReturn($mockApiData);

    // Act
    $products = $this->productService->getProductsByCategory('jewelery', 5, 'desc');

    // Assert
    $this->assertCount(2, $products);
    $this->assertContainsOnlyInstancesOf(ProductDTO::class, $products);
    foreach ($products as $product) {
      $this->assertEquals('jewelery', $product->category);
    }
  }

  public function testGetProductsByCategoryWithPriceFilter(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'Cheap Jewelry', 50.0, 'jewelery'),
      $this->createMockApiData(2, 'Expensive Jewelry', 300.0, 'jewelery'),
      $this->createMockApiData(3, 'Mid Jewelry', 150.0, 'jewelery')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getProductsByCategory')
      ->with('jewelery', null, null)
      ->willReturn($mockApiData);

    // Act: Filter by price range
    $products = $this->productService->getProductsByCategory(
      category: 'jewelery',
      minPrice: 100.0,
      maxPrice: 250.0
    );

    // Assert: Only mid-priced jewelry should match
    $this->assertCount(1, $products);
    $this->assertEquals('Mid Jewelry', $products[0]->title);
    $this->assertEquals(150.0, $products[0]->price);
  }

  public function testGetCategories(): void
  {
    // Arrange
    $mockCategories = ['electronics', 'jewelery', "men's clothing", "women's clothing"];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getCategories')
      ->willReturn($mockCategories);

    // Act
    $categories = $this->productService->getCategories();

    // Assert
    $this->assertEquals($mockCategories, $categories);
    $this->assertCount(4, $categories);
    $this->assertContains('electronics', $categories);
  }

  public function testSearchProducts(): void
  {
    // Arrange: Products with searchable content
    $mockApiData = [
      $this->createMockApiData(1, 'Smartphone iPhone', 699.0, 'electronics'),
      $this->createMockApiData(2, 'Android Phone', 399.0, 'electronics'),
      $this->createMockApiData(3, 'Laptop Computer', 999.0, 'electronics'),
      $this->createMockApiData(4, 'T-Shirt Cotton', 25.0, 'clothing')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act: Search for "phone"
    $products = $this->productService->searchProducts('phone');

    // Assert: Should find both phone products
    $this->assertCount(2, $products);
    $this->assertEquals('Smartphone iPhone', $products[0]->title);
    $this->assertEquals('Android Phone', $products[1]->title);
  }

  public function testSearchProductsCaseInsensitive(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'LAPTOP Computer', 999.0, 'electronics'),
      $this->createMockApiData(2, 'Gaming laptop', 1299.0, 'electronics')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act: Search with different case
    $products = $this->productService->searchProducts('LaPtOp');

    // Assert: Should find both regardless of case
    $this->assertCount(2, $products);
  }

  public function testSearchProductsInDescription(): void
  {
    // Arrange: Product with searchable description
    $mockApiData = [
      [
        'id' => 1,
        'title' => 'Mysterious Item',
        'price' => 50.0,
        'description' => 'This amazing smartphone has great features',
        'category' => 'electronics',
        'image' => 'image.jpg'
      ]
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act: Search for term in description
    $products = $this->productService->searchProducts('smartphone');

    // Assert: Should find product by description match
    $this->assertCount(1, $products);
    $this->assertEquals('Mysterious Item', $products[0]->title);
  }

  public function testSearchProductsInCategory(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'Gold Ring', 299.0, 'jewelery'),
      $this->createMockApiData(2, 'Silver Necklace', 199.0, 'jewelery'),
      $this->createMockApiData(3, 'Phone Case', 25.0, 'electronics')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act: Search for category name
    $products = $this->productService->searchProducts('jewelery');

    // Assert: Should find products by category match
    $this->assertCount(2, $products);
    foreach ($products as $product) {
      $this->assertEquals('jewelery', $product->category);
    }
  }

  public function testSearchProductsWithFilters(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'Cheap Phone', 100.0, 'electronics'),
      $this->createMockApiData(2, 'Expensive Phone', 800.0, 'electronics'),
      $this->createMockApiData(3, 'Phone Case', 25.0, 'accessories')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(10, 'asc')
      ->willReturn($mockApiData);

    // Act: Search with additional filters
    $products = $this->productService->searchProducts(
      query: 'phone',
      limit: 10,
      sort: 'asc',
      category: 'electronics',
      minPrice: 50.0,
      maxPrice: 500.0
    );

    // Assert: Should apply all filters
    $this->assertCount(1, $products);
    $this->assertEquals('Cheap Phone', $products[0]->title);
    $this->assertEquals(100.0, $products[0]->price);
    $this->assertEquals('electronics', $products[0]->category);
  }

  public function testSearchProductsNoResults(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'Product A', 50.0, 'category1'),
      $this->createMockApiData(2, 'Product B', 75.0, 'category2')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act: Search for non-existent term
    $products = $this->productService->searchProducts('nonexistent');

    // Assert: Should return empty array
    $this->assertEmpty($products);
  }

  public function testGetProductStats(): void
  {
    // Arrange
    $mockApiData = [
      $this->createMockApiData(1, 'Product 1', 25.0, 'electronics'),
      $this->createMockApiData(2, 'Product 2', 75.0, 'clothing'),
      $this->createMockApiData(3, 'Product 3', 150.0, 'jewelery'),
      $this->createMockApiData(4, 'Product 4', 200.0, 'electronics')
    ];

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act
    $stats = $this->productService->getProductStats();

    // Assert
    $this->assertIsArray($stats);
    $this->assertEquals(4, $stats['total_products']);
    $this->assertEquals(25.0, $stats['min_price']);
    $this->assertEquals(200.0, $stats['max_price']);
    $this->assertEquals(112.5, $stats['avg_price']); // (25+75+150+200)/4
  }

  public function testGetProductStatsEmptyProducts(): void
  {
    // Arrange: No products
    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn([]);

    // Act
    $stats = $this->productService->getProductStats();

    // Assert
    $this->assertEquals(0, $stats['total_products']);
    $this->assertEquals(0, $stats['min_price']);
    $this->assertEquals(1000, $stats['max_price']); // Default fallback
    $this->assertEquals(0, $stats['avg_price']);
  }

  /**
   * Test data provider for various filter combinations
   */
  public static function filterScenarioProvider(): array
  {
    return [
      'Price filter only' => [
        ['minPrice' => 50.0, 'maxPrice' => 100.0], // filters
        [ // mockData
          ['id' => 1, 'title' => 'Cheap', 'price' => 25.0, 'category' => 'test'],
          ['id' => 2, 'title' => 'Perfect', 'price' => 75.0, 'category' => 'test'],
          ['id' => 3, 'title' => 'Expensive', 'price' => 150.0, 'category' => 'test']
        ],
        1, // expectedCount
        'Perfect' // expectedTitle
      ],
      'Category filter only' => [
        ['category' => 'electronics'], // filters
        [ // mockData
          ['id' => 1, 'title' => 'Phone', 'price' => 100.0, 'category' => 'electronics'],
          ['id' => 2, 'title' => 'Shirt', 'price' => 25.0, 'category' => 'clothing'],
          ['id' => 3, 'title' => 'Laptop', 'price' => 500.0, 'category' => 'electronics']
        ],
        2, // expectedCount
        null // expectedTitle - Multiple results
      ],
      'Combined filters' => [
        ['category' => 'electronics', 'minPrice' => 200.0], // filters
        [ // mockData
          ['id' => 1, 'title' => 'Cheap Phone', 'price' => 100.0, 'category' => 'electronics'],
          ['id' => 2, 'title' => 'Expensive Phone', 'price' => 800.0, 'category' => 'electronics'],
          ['id' => 3, 'title' => 'Expensive Shirt', 'price' => 300.0, 'category' => 'clothing']
        ],
        1, // expectedCount
        'Expensive Phone' // expectedTitle
      ]
    ];
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('filterScenarioProvider')]
  public function testFilterScenarios(array $filters, array $mockDataRaw, int $expectedCount, ?string $expectedTitle): void
  {
    // Arrange: Convert raw data to proper format
    $mockApiData = [];
    foreach ($mockDataRaw as $item) {
      $mockApiData[] = $this->createMockApiData(
        $item['id'],
        $item['title'],
        $item['price'],
        $item['category']
      );
    }

    $this->fakeStoreApiService
      ->expects($this->once())
      ->method('getAllProducts')
      ->with(null, null)
      ->willReturn($mockApiData);

    // Act
    $products = $this->productService->getAllProducts(
      limit: null,
      sort: null,
      category: $filters['category'] ?? null,
      minPrice: $filters['minPrice'] ?? null,
      maxPrice: $filters['maxPrice'] ?? null
    );

    // Assert
    $this->assertCount($expectedCount, $products);

    if ($expectedTitle && $expectedCount === 1) {
      $this->assertEquals($expectedTitle, $products[0]->title);
    }
  }
}
