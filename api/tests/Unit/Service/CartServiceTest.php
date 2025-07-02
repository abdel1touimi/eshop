<?php

namespace App\Tests\Unit\Service;

use App\Service\CartService;
use App\Service\ProductService;
use App\DTO\ProductDTO;
use App\DTO\CartDTO;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class CartServiceTest extends TestCase
{
  private CartService $cartService;
  private ProductService|MockObject $productService;
  private RequestStack|MockObject $requestStack;
  private SessionInterface|MockObject $session;

  private const SESSION_KEY = 'test_cart';
  private const DISCOUNT_THRESHOLD = 200.0;
  private const DISCOUNT_RATE = 0.10;
  private const DEFAULT_CURRENCY = 'EUR';

  protected function setUp(): void
  {
    // Create mocks
    $this->productService = $this->createMock(ProductService::class);
    $this->requestStack = $this->createMock(RequestStack::class);
    $this->session = $this->createMock(SessionInterface::class);

    // Configure RequestStack to return our mocked session
    $this->requestStack
      ->method('getSession')
      ->willReturn($this->session);

    // Create CartService with mocked dependencies
    $this->cartService = new CartService(
      $this->productService,
      $this->requestStack,
      self::SESSION_KEY,
      self::DISCOUNT_THRESHOLD,
      self::DISCOUNT_RATE,
      self::DEFAULT_CURRENCY
    );
  }

  private function createMockProduct(int $id, string $title, float $price): ProductDTO
  {
    return new ProductDTO(
      id: $id,
      title: $title,
      price: $price,
      currency: 'EUR',
      description: "Description for {$title}",
      category: 'test',
      image: 'https://example.com/image.jpg',
      rating: ['rate' => 4.0, 'count' => 100]
    );
  }

  public function testGetCartWhenEmpty(): void
  {
    // Arrange: Empty cart session
    $this->session
      ->expects($this->once())
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn([]);

    // Act
    $cart = $this->cartService->getCart();

    // Assert
    $this->assertInstanceOf(CartDTO::class, $cart);
    $this->assertEmpty($cart->items);
    $this->assertEquals(0.0, $cart->totals->subtotal);
    $this->assertEquals(0.0, $cart->totals->total);
    $this->assertFalse($cart->totals->hasDiscount);
  }

  public function testGetCartWithItems(): void
  {
    // Arrange: Cart with items in session
    $cartData = [
      1 => ['quantity' => 2],
      2 => ['quantity' => 1]
    ];

    $this->session
      ->expects($this->once())
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($cartData);

    // Mock product service calls
    $product1 = $this->createMockProduct(1, 'Product 1', 50.0);
    $product2 = $this->createMockProduct(2, 'Product 2', 30.0);

    $this->productService
      ->expects($this->exactly(2))
      ->method('getProduct')
      ->willReturnMap([
        [1, $product1],
        [2, $product2]
      ]);

    // Act
    $cart = $this->cartService->getCart();

    // Assert
    $this->assertCount(2, $cart->items);
    $this->assertEquals(130.0, $cart->totals->subtotal); // (50*2) + (30*1)
    $this->assertEquals(130.0, $cart->totals->total); // No discount (under €200)
    $this->assertFalse($cart->totals->hasDiscount);
  }

  public function testGetCartWithDiscountApplied(): void
  {
    // Arrange: Cart with items over discount threshold
    $cartData = [
      1 => ['quantity' => 3],
      2 => ['quantity' => 2]
    ];

    $this->session
      ->expects($this->once())
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($cartData);

    // Mock expensive products
    $product1 = $this->createMockProduct(1, 'Expensive Product 1', 80.0);
    $product2 = $this->createMockProduct(2, 'Expensive Product 2', 60.0);

    $this->productService
      ->expects($this->exactly(2))
      ->method('getProduct')
      ->willReturnMap([
        [1, $product1],
        [2, $product2]
      ]);

    // Act
    $cart = $this->cartService->getCart();

    // Assert
    $this->assertCount(2, $cart->items);
    $this->assertEquals(360.0, $cart->totals->subtotal); // (80*3) + (60*2) = 240 + 120
    $this->assertEquals(36.0, $cart->totals->discount); // 10% of 360
    $this->assertEquals(324.0, $cart->totals->total); // 360 - 36
    $this->assertTrue($cart->totals->hasDiscount);
  }

  public function testGetCartHandlesProductNotFound(): void
  {
    // Arrange: Cart with invalid product
    $cartData = [
      999 => ['quantity' => 1] // Non-existent product
    ];

    $this->session
      ->expects($this->exactly(3)) // getCart calls session multiple times: initial get, during cleanup, final get
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturnOnConsecutiveCalls($cartData, $cartData, []); // cartData twice, then empty after cleanup

    // Mock product service to throw exception
    $this->productService
      ->expects($this->once())
      ->method('getProduct')
      ->with(999)
      ->willThrowException(new \Exception('Product not found'));

    // Expect removeFromCart to be called (via session manipulation)
    $this->session
      ->expects($this->once())
      ->method('set')
      ->with(self::SESSION_KEY, []); // Should remove invalid product

    // Act
    $cart = $this->cartService->getCart();

    // Assert: Invalid product should be removed, cart should be empty
    $this->assertEmpty($cart->items);
  }

  public function testAddToCart(): void
  {
    // Arrange: Start with empty cart
    $this->session
      ->expects($this->exactly(2))
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn([]); // Empty initially

    // Mock product exists (only called once for validation, getCart will be mocked separately)
    $product = $this->createMockProduct(1, 'New Product', 25.0);
    $this->productService
      ->expects($this->once()) // Only for validation in addToCart
      ->method('getProduct')
      ->with(1)
      ->willReturn($product);

    // Expect session to be updated
    $this->session
      ->expects($this->once())
      ->method('set')
      ->with(self::SESSION_KEY, [1 => ['quantity' => 2]]);

    // Act
    $cart = $this->cartService->addToCart(1, 2);

    // Assert
    $this->assertInstanceOf(CartDTO::class, $cart);
  }

  public function testAddToCartExistingProduct(): void
  {
    // Arrange: Cart already has the product
    $existingCartData = [1 => ['quantity' => 1]];

    $this->session
      ->expects($this->exactly(2))
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($existingCartData);

    // Mock product exists
    $product = $this->createMockProduct(1, 'Existing Product', 50.0);
    $this->productService
      ->expects($this->exactly(2))
      ->method('getProduct')
      ->with(1)
      ->willReturn($product);

    // Expect session to be updated with incremented quantity
    $this->session
      ->expects($this->once())
      ->method('set')
      ->with(self::SESSION_KEY, [1 => ['quantity' => 3]]); // 1 + 2

    // Act
    $cart = $this->cartService->addToCart(1, 2);

    // Assert
    $this->assertInstanceOf(CartDTO::class, $cart);
  }

  public function testAddToCartProductNotFound(): void
  {
    // Arrange: Product doesn't exist
    $this->productService
      ->expects($this->once())
      ->method('getProduct')
      ->with(999)
      ->willThrowException(new \Exception('Product not found'));

    // Act & Assert
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Product not found');

    $this->cartService->addToCart(999, 1);
  }

  public function testUpdateCartItem(): void
  {
    // Arrange: Existing cart data
    $cartData = [1 => ['quantity' => 2]];

    $this->session
      ->expects($this->exactly(2))
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($cartData);

    // Mock product for getCart call
    $product = $this->createMockProduct(1, 'Update Product', 30.0);
    $this->productService
      ->expects($this->once())
      ->method('getProduct')
      ->with(1)
      ->willReturn($product);

    // Expect session update
    $this->session
      ->expects($this->once())
      ->method('set')
      ->with(self::SESSION_KEY, [1 => ['quantity' => 5]]);

    // Act
    $cart = $this->cartService->updateCartItem(1, 5);

    // Assert
    $this->assertInstanceOf(CartDTO::class, $cart);
  }

  public function testUpdateCartItemNotFound(): void
  {
    // Arrange: Empty cart
    $this->session
      ->expects($this->once())
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn([]);

    // Act & Assert
    $this->expectException(\Exception::class);
    $this->expectExceptionMessage('Product not found in cart');

    $this->cartService->updateCartItem(999, 2);
  }

  public function testUpdateCartItemWithZeroQuantity(): void
  {
    // Arrange: Existing cart
    $cartData = [1 => ['quantity' => 2]];

    $this->session
      ->expects($this->exactly(2))
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($cartData, []); // Second call after removal

    // Expect removal (set session without the item)
    $this->session
      ->expects($this->once())
      ->method('set')
      ->with(self::SESSION_KEY, []);

    // Act: Update with 0 quantity should remove item
    $cart = $this->cartService->updateCartItem(1, 0);

    // Assert
    $this->assertInstanceOf(CartDTO::class, $cart);
  }

  public function testRemoveFromCart(): void
  {
    // Arrange: Cart with item to remove
    $cartData = [
      1 => ['quantity' => 2],
      2 => ['quantity' => 1]
    ];

    $this->session
      ->expects($this->exactly(2))
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($cartData, [2 => ['quantity' => 1]]); // After removal

    // Expect session update (item 1 removed)
    $this->session
      ->expects($this->once())
      ->method('set')
      ->with(self::SESSION_KEY, [2 => ['quantity' => 1]]);

    // Mock remaining product
    $product2 = $this->createMockProduct(2, 'Remaining Product', 20.0);
    $this->productService
      ->expects($this->once())
      ->method('getProduct')
      ->with(2)
      ->willReturn($product2);

    // Act
    $cart = $this->cartService->removeFromCart(1);

    // Assert
    $this->assertInstanceOf(CartDTO::class, $cart);
  }

  public function testRemoveFromCartNonExistentItem(): void
  {
    // Arrange: Cart without the item
    $cartData = [1 => ['quantity' => 1]];

    $this->session
      ->expects($this->exactly(2))
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($cartData); // Item 999 doesn't exist

    // Should not call set since item doesn't exist
    $this->session
      ->expects($this->never())
      ->method('set');

    // Mock existing product for getCart
    $product = $this->createMockProduct(1, 'Existing Product', 40.0);
    $this->productService
      ->expects($this->once())
      ->method('getProduct')
      ->with(1)
      ->willReturn($product);

    // Act: Try to remove non-existent item
    $cart = $this->cartService->removeFromCart(999);

    // Assert: Should return cart unchanged
    $this->assertInstanceOf(CartDTO::class, $cart);
  }

  public function testClearCart(): void
  {
    // Arrange & Act
    $this->session
      ->expects($this->once())
      ->method('remove')
      ->with(self::SESSION_KEY);

    $this->session
      ->expects($this->once())
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn([]);

    // Act
    $cart = $this->cartService->clearCart();

    // Assert
    $this->assertInstanceOf(CartDTO::class, $cart);
    $this->assertEmpty($cart->items);
    $this->assertEquals(0.0, $cart->totals->total);
  }

  public function testGetCartItemsCount(): void
  {
    // Arrange: Cart with multiple items
    $cartData = [
      1 => ['quantity' => 3],
      2 => ['quantity' => 2],
      3 => ['quantity' => 1]
    ];

    $this->session
      ->expects($this->once())
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($cartData);

    // Act
    $count = $this->cartService->getCartItemsCount();

    // Assert
    $this->assertEquals(6, $count); // 3 + 2 + 1
  }

  public function testGetCartItemsCountEmptyCart(): void
  {
    // Arrange: Empty cart
    $this->session
      ->expects($this->once())
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn([]);

    // Act
    $count = $this->cartService->getCartItemsCount();

    // Assert
    $this->assertEquals(0, $count);
  }

  /**
   * Test various cart scenarios with different quantities and discounts
   */
  public static function cartScenarioProvider(): array
  {
    return [
      'Single item under threshold' => [
        [1 => ['quantity' => 2]], // Cart data
        [1 => ['price' => 75.0, 'title' => 'Item 1']], // Products
        150.0, // Expected subtotal
        false // Has discount
      ],
      'Multiple items at threshold' => [
        [1 => ['quantity' => 4], 2 => ['quantity' => 2]], // Cart data
        [1 => ['price' => 30.0, 'title' => 'Item 1'], 2 => ['price' => 40.0, 'title' => 'Item 2']], // Products
        200.0, // Expected subtotal (30*4 + 40*2 = 120 + 80)
        true // Has discount
      ],
      'High value cart' => [
        [1 => ['quantity' => 1], 2 => ['quantity' => 1]], // Cart data
        [1 => ['price' => 300.0, 'title' => 'Expensive 1'], 2 => ['price' => 200.0, 'title' => 'Expensive 2']], // Products
        500.0, // Expected subtotal
        true // Has discount
      ]
    ];
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('cartScenarioProvider')]
  public function testCartScenarios(
    array $cartData,
    array $productData,
    float $expectedSubtotal,
    bool $expectedHasDiscount
  ): void {
    // Arrange
    $this->session
      ->expects($this->once())
      ->method('get')
      ->with(self::SESSION_KEY, [])
      ->willReturn($cartData);

    // Set up product service mocks
    $productServiceMap = [];
    foreach ($productData as $id => $data) {
      $product = $this->createMockProduct($id, $data['title'], $data['price']);
      $productServiceMap[] = [$id, $product];
    }

    $this->productService
      ->expects($this->exactly(count($productData)))
      ->method('getProduct')
      ->willReturnMap($productServiceMap);

    // Act
    $cart = $this->cartService->getCart();

    // Assert
    $this->assertEquals($expectedSubtotal, $cart->totals->subtotal);
    $this->assertEquals($expectedHasDiscount, $cart->totals->hasDiscount);

    if ($expectedHasDiscount) {
      $this->assertGreaterThan(0, $cart->totals->discount);
      $this->assertLessThan($cart->totals->subtotal, $cart->totals->total);
    } else {
      $this->assertEquals(0, $cart->totals->discount);
      $this->assertEquals($cart->totals->subtotal, $cart->totals->total);
    }
  }
}
