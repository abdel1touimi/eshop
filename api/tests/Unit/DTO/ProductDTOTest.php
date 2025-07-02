<?php

namespace App\Tests\Unit\DTO;

use App\DTO\ProductDTO;
use PHPUnit\Framework\TestCase;

class ProductDTOTest extends TestCase
{
  public function testFromFakeStoreApiWithCompleteData(): void
  {
    // Arrange: Complete API response data
    $apiData = [
      'id' => 1,
      'title' => 'Fjallraven - Foldsack No. 1 Backpack',
      'price' => 109.95,
      'description' => 'Your perfect pack for everyday use and walks in the forest.',
      'category' => "men's clothing",
      'image' => 'https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg',
      'rating' => [
        'rate' => 3.9,
        'count' => 120
      ]
    ];

    // Act
    $product = ProductDTO::fromFakeStoreApi($apiData);

    // Assert
    $this->assertEquals(1, $product->id);
    $this->assertEquals('Fjallraven - Foldsack No. 1 Backpack', $product->title);
    $this->assertEquals(109.95, $product->price);
    $this->assertEquals('EUR', $product->currency); // Default currency
    $this->assertEquals('Your perfect pack for everyday use and walks in the forest.', $product->description);
    $this->assertEquals("men's clothing", $product->category);
    $this->assertEquals('https://fakestoreapi.com/img/81fPKd-2AYL._AC_SL1500_.jpg', $product->image);
    $this->assertEquals(['rate' => 3.9, 'count' => 120], $product->rating);
  }

  public function testFromFakeStoreApiWithCustomCurrency(): void
  {
    // Arrange: Test with custom currency
    $apiData = [
      'id' => 2,
      'title' => 'Test Product',
      'price' => 19.99,
      'description' => 'A test product',
      'category' => 'electronics',
      'image' => 'https://example.com/image.jpg'
    ];

    // Act
    $product = ProductDTO::fromFakeStoreApi($apiData, 'USD');

    // Assert
    $this->assertEquals('USD', $product->currency);
    $this->assertEquals(19.99, $product->price);
  }

  public function testFromFakeStoreApiWithoutRating(): void
  {
    // Arrange: API response without rating
    $apiData = [
      'id' => 3,
      'title' => 'Product Without Rating',
      'price' => 25.50,
      'description' => 'This product has no rating yet',
      'category' => 'books',
      'image' => 'https://example.com/book.jpg'
    ];

    // Act
    $product = ProductDTO::fromFakeStoreApi($apiData);

    // Assert
    $this->assertNull($product->rating);
    $this->assertEquals(3, $product->id);
    $this->assertEquals('Product Without Rating', $product->title);
  }

  public function testFromFakeStoreApiWithNullRating(): void
  {
    // Arrange: API response with explicit null rating
    $apiData = [
      'id' => 4,
      'title' => 'Product With Null Rating',
      'price' => 15.00,
      'description' => 'Rating is null',
      'category' => 'toys',
      'image' => 'https://example.com/toy.jpg',
      'rating' => null
    ];

    // Act
    $product = ProductDTO::fromFakeStoreApi($apiData);

    // Assert
    $this->assertNull($product->rating);
  }

  public function testFromFakeStoreApiWithEmptyRating(): void
  {
    // Arrange: API response with empty rating array
    $apiData = [
      'id' => 5,
      'title' => 'Product With Empty Rating',
      'price' => 12.99,
      'description' => 'Rating is empty array',
      'category' => 'home',
      'image' => 'https://example.com/home.jpg',
      'rating' => []
    ];

    // Act
    $product = ProductDTO::fromFakeStoreApi($apiData);

    // Assert
    $this->assertEquals([], $product->rating);
  }

  public function testFromFakeStoreApiWithFloatPrice(): void
  {
    // Arrange: Test price type conversion
    $apiData = [
      'id' => 6,
      'title' => 'Float Price Product',
      'price' => '99.99', // String price (like from API)
      'description' => 'Test price conversion',
      'category' => 'electronics',
      'image' => 'https://example.com/electronics.jpg'
    ];

    // Act
    $product = ProductDTO::fromFakeStoreApi($apiData);

    // Assert
    $this->assertIsFloat($product->price);
    $this->assertEquals(99.99, $product->price);
  }

  public function testToArray(): void
  {
    // Arrange: Create product with all fields
    $product = new ProductDTO(
      id: 10,
      title: 'Complete Product',
      price: 299.99,
      currency: 'EUR',
      description: 'A complete product with all fields',
      category: 'premium',
      image: 'https://example.com/premium.jpg',
      rating: ['rate' => 4.8, 'count' => 250]
    );

    // Act
    $array = $product->toArray();

    // Assert
    $expected = [
      'id' => 10,
      'title' => 'Complete Product',
      'price' => 299.99,
      'currency' => 'EUR',
      'description' => 'A complete product with all fields',
      'category' => 'premium',
      'image' => 'https://example.com/premium.jpg',
      'rating' => ['rate' => 4.8, 'count' => 250]
    ];

    $this->assertEquals($expected, $array);
  }

  public function testToArrayWithNullRating(): void
  {
    // Arrange: Create product without rating
    $product = new ProductDTO(
      id: 11,
      title: 'Product Without Rating',
      price: 49.99,
      currency: 'USD',
      description: 'No rating provided',
      category: 'misc',
      image: 'https://example.com/misc.jpg',
      rating: null
    );

    // Act
    $array = $product->toArray();

    // Assert
    $this->assertArrayHasKey('rating', $array);
    $this->assertNull($array['rating']);
  }

  /**
   * Data provider for testing various API response scenarios
   */
  public static function fakeStoreApiDataProvider(): array
  {
    return [
      'Jewelry item' => [
        [
          'id' => 7,
          'title' => 'White Gold Plated Princess',
          'price' => 9.99,
          'description' => 'Classic Created Wedding Engagement Solitaire Diamond Promise Ring',
          'category' => 'jewelery',
          'image' => 'https://fakestoreapi.com/img/71YAIFU48IL._AC_UL640_QL65_ML3_.jpg',
          'rating' => ['rate' => 3, 'count' => 400]
        ],
        'jewelery',
        3
      ],
      'Electronics item' => [
        [
          'id' => 8,
          'title' => 'Portable External Hard Drive',
          'price' => 64,
          'description' => 'USB 3.0 and USB 2.0 Compatibility Fast data transfers',
          'category' => 'electronics',
          'image' => 'https://fakestoreapi.com/img/61IBBVJvSDL._AC_SY879_.jpg',
          'rating' => ['rate' => 3.3, 'count' => 203]
        ],
        'electronics',
        3.3
      ],
      'Womens clothing' => [
        [
          'id' => 9,
          'title' => 'Womens T-Shirt',
          'price' => 7.95,
          'description' => 'Great outerwear jackets for Spring/Autumn/Winter',
          'category' => "women's clothing",
          'image' => 'https://fakestoreapi.com/img/51Y5NI-I5jL._AC_UX679_.jpg',
          'rating' => ['rate' => 2.1, 'count' => 235]
        ],
        "women's clothing",
        2.1
      ]
    ];
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('fakeStoreApiDataProvider')]
  public function testFromFakeStoreApiVariousProducts(array $apiData, string $expectedCategory, float $expectedRating): void
  {
    // Act
    $product = ProductDTO::fromFakeStoreApi($apiData);

    // Assert
    $this->assertEquals($apiData['id'], $product->id);
    $this->assertEquals($apiData['title'], $product->title);
    $this->assertEquals((float)$apiData['price'], $product->price);
    $this->assertEquals('EUR', $product->currency);
    $this->assertEquals($apiData['description'], $product->description);
    $this->assertEquals($expectedCategory, $product->category);
    $this->assertEquals($apiData['image'], $product->image);
    $this->assertEquals($expectedRating, $product->rating['rate']);
    $this->assertIsInt($product->rating['count']);
  }

  public function testProductDTOImmutability(): void
  {
    // Arrange
    $product = new ProductDTO(
      id: 12,
      title: 'Immutable Product',
      price: 199.99,
      currency: 'EUR',
      description: 'Testing immutability',
      category: 'test',
      image: 'https://example.com/test.jpg'
    );

    // Act & Assert: Properties should be readonly
    $this->assertEquals(12, $product->id);
    $this->assertEquals('Immutable Product', $product->title);

    // The readonly properties ensure immutability at PHP level
    // This test verifies the DTO structure is correctly immutable
    $reflection = new \ReflectionClass($product);
    $properties = $reflection->getProperties();

    foreach ($properties as $property) {
      $this->assertTrue($property->isReadOnly(), "Property {$property->getName()} should be readonly");
    }
  }

  /**
   * Test edge cases with unusual but valid API data
   */
  public static function edgeCaseDataProvider(): array
  {
    return [
      'Zero price' => [
        ['id' => 100, 'title' => 'Free Item', 'price' => 0, 'description' => 'Free', 'category' => 'free', 'image' => 'img.jpg'],
        0.0
      ],
      'Very high price' => [
        ['id' => 101, 'title' => 'Expensive Item', 'price' => 99999.99, 'description' => 'Luxury', 'category' => 'luxury', 'image' => 'luxury.jpg'],
        99999.99
      ],
      'Empty title' => [
        ['id' => 102, 'title' => '', 'price' => 10, 'description' => 'No title', 'category' => 'misc', 'image' => 'notitle.jpg'],
        ''
      ],
      'Very long description' => [
        ['id' => 103, 'title' => 'Long Desc', 'price' => 25, 'description' => str_repeat('A very long description. ', 100), 'category' => 'books', 'image' => 'book.jpg'],
        str_repeat('A very long description. ', 100)
      ]
    ];
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('edgeCaseDataProvider')]
  public function testFromFakeStoreApiEdgeCases(array $apiData, mixed $expectedValue): void
  {
    // Act
    $product = ProductDTO::fromFakeStoreApi($apiData);

    // Assert based on what we're testing
    if (is_float($expectedValue)) {
      $this->assertEquals($expectedValue, $product->price);
    } elseif (is_string($expectedValue)) {
      if ($apiData['title'] === '') {
        $this->assertEquals('', $product->title);
      } else {
        $this->assertEquals($expectedValue, $product->description);
      }
    }

    // Always ensure basic structure is maintained
    $this->assertIsInt($product->id);
    $this->assertIsString($product->title);
    $this->assertIsFloat($product->price);
    $this->assertEquals('EUR', $product->currency);
  }
}
