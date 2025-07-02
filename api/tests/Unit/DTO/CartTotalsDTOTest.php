<?php

namespace App\Tests\Unit\DTO;

use App\DTO\CartTotalsDTO;
use App\DTO\CartItemDTO;
use PHPUnit\Framework\TestCase;

class CartTotalsDTOTest extends TestCase
{
  private function createCartItem(int $productId, float $price, int $quantity): CartItemDTO
  {
    return new CartItemDTO(
      productId: $productId,
      title: "Test Product {$productId}",
      price: $price,
      currency: 'EUR',
      quantity: $quantity,
      image: 'test-image.jpg',
      totalPrice: $price * $quantity
    );
  }

  public function testCalculateFromItemsWithoutDiscount(): void
  {
    // Arrange: Cart under €200 threshold
    $cartItems = [
      $this->createCartItem(1, 50.0, 2),  // €100
      $this->createCartItem(2, 30.0, 1),  // €30
    ];
    $discountThreshold = 200.0;
    $discountRate = 0.10;
    $currency = 'EUR';

    // Act
    $totals = CartTotalsDTO::calculateFromItems(
      $cartItems,
      $discountThreshold,
      $discountRate,
      $currency
    );

    // Assert
    $this->assertEquals(130.0, $totals->subtotal);
    $this->assertEquals(0.0, $totals->discount);
    $this->assertEquals(130.0, $totals->total);
    $this->assertFalse($totals->hasDiscount);
    $this->assertEquals('EUR', $totals->currency);
  }

  public function testCalculateFromItemsWithDiscountExactThreshold(): void
  {
    // Arrange: Cart exactly at €200 threshold
    $cartItems = [
      $this->createCartItem(1, 100.0, 2),  // €200
    ];
    $discountThreshold = 200.0;
    $discountRate = 0.10;
    $currency = 'EUR';

    // Act
    $totals = CartTotalsDTO::calculateFromItems(
      $cartItems,
      $discountThreshold,
      $discountRate,
      $currency
    );

    // Assert
    $this->assertEquals(200.0, $totals->subtotal);
    $this->assertEquals(20.0, $totals->discount);  // 10% of €200
    $this->assertEquals(180.0, $totals->total);
    $this->assertTrue($totals->hasDiscount);
    $this->assertEquals('EUR', $totals->currency);
  }

  public function testCalculateFromItemsWithDiscountAboveThreshold(): void
  {
    // Arrange: Cart well above €200 threshold
    $cartItems = [
      $this->createCartItem(1, 150.0, 2),  // €300
      $this->createCartItem(2, 75.0, 2),   // €150
    ];
    $discountThreshold = 200.0;
    $discountRate = 0.10;
    $currency = 'EUR';

    // Act
    $totals = CartTotalsDTO::calculateFromItems(
      $cartItems,
      $discountThreshold,
      $discountRate,
      $currency
    );

    // Assert
    $this->assertEquals(450.0, $totals->subtotal);
    $this->assertEquals(45.0, $totals->discount);  // 10% of €450
    $this->assertEquals(405.0, $totals->total);
    $this->assertTrue($totals->hasDiscount);
    $this->assertEquals('EUR', $totals->currency);
  }

  public function testCalculateFromItemsWithEmptyCart(): void
  {
    // Arrange: Empty cart
    $cartItems = [];
    $discountThreshold = 200.0;
    $discountRate = 0.10;
    $currency = 'EUR';

    // Act
    $totals = CartTotalsDTO::calculateFromItems(
      $cartItems,
      $discountThreshold,
      $discountRate,
      $currency
    );

    // Assert
    $this->assertEquals(0.0, $totals->subtotal);
    $this->assertEquals(0.0, $totals->discount);
    $this->assertEquals(0.0, $totals->total);
    $this->assertFalse($totals->hasDiscount);
    $this->assertEquals('EUR', $totals->currency);
  }

  public function testCalculateFromItemsWithDifferentCurrency(): void
  {
    // Arrange: Test with different currency
    $cartItems = [
      $this->createCartItem(1, 250.0, 1),  // Above threshold
    ];
    $discountThreshold = 200.0;
    $discountRate = 0.15;  // 15% discount
    $currency = 'USD';

    // Act
    $totals = CartTotalsDTO::calculateFromItems(
      $cartItems,
      $discountThreshold,
      $discountRate,
      $currency
    );

    // Assert
    $this->assertEquals(250.0, $totals->subtotal);
    $this->assertEquals(37.5, $totals->discount);  // 15% of €250
    $this->assertEquals(212.5, $totals->total);
    $this->assertTrue($totals->hasDiscount);
    $this->assertEquals('USD', $totals->currency);
  }

  public function testCalculateFromItemsWithFloatingPointPrecision(): void
  {
    // Arrange: Test rounding behavior with floating point numbers
    $cartItems = [
      $this->createCartItem(1, 66.67, 3),  // €200.01 (slightly above threshold)
    ];
    $discountThreshold = 200.0;
    $discountRate = 0.10;
    $currency = 'EUR';

    // Act
    $totals = CartTotalsDTO::calculateFromItems(
      $cartItems,
      $discountThreshold,
      $discountRate,
      $currency
    );

    // Assert
    $this->assertEquals(200.01, $totals->subtotal);
    $this->assertEquals(20.0, $totals->discount, '', 0.01);  // Allow small floating point difference
    $this->assertEquals(180.01, $totals->total, '', 0.01);
    $this->assertTrue($totals->hasDiscount);
  }

  public function testCalculateFromItemsJustUnderThreshold(): void
  {
    // Arrange: Cart just under threshold (edge case)
    $cartItems = [
      $this->createCartItem(1, 199.99, 1),  // €199.99 (just under €200)
    ];
    $discountThreshold = 200.0;
    $discountRate = 0.10;
    $currency = 'EUR';

    // Act
    $totals = CartTotalsDTO::calculateFromItems(
      $cartItems,
      $discountThreshold,
      $discountRate,
      $currency
    );

    // Assert
    $this->assertEquals(199.99, $totals->subtotal);
    $this->assertEquals(0.0, $totals->discount);
    $this->assertEquals(199.99, $totals->total);
    $this->assertFalse($totals->hasDiscount);
  }

  public function testToArray(): void
  {
    // Arrange
    $totals = new CartTotalsDTO(
      subtotal: 250.0,
      discount: 25.0,
      total: 225.0,
      hasDiscount: true,
      currency: 'EUR'
    );

    // Act
    $array = $totals->toArray();

    // Assert
    $expected = [
      'subtotal' => 250.0,
      'discount' => 25.0,
      'total' => 225.0,
      'hasDiscount' => true,
      'currency' => 'EUR'
    ];

    $this->assertEquals($expected, $array);
  }

  /**
   * Test that discount threshold and rate validation work correctly
   * This tests different business rules
   */
  public static function discountScenarioProvider(): array
  {
    return [
      'No discount - empty cart' => [[], 0.0, false],
      'No discount - under threshold' => [[['price' => 50.0, 'qty' => 3]], 150.0, false],
      'Discount - exact threshold' => [[['price' => 100.0, 'qty' => 2]], 200.0, true],
      'Discount - above threshold' => [[['price' => 150.0, 'qty' => 2]], 300.0, true],
      'Discount - multiple items' => [
        [
          ['price' => 80.0, 'qty' => 2],   // €160
          ['price' => 25.0, 'qty' => 2]    // €50
        ],
        210.0,
        true
      ],
    ];
  }

  #[\PHPUnit\Framework\Attributes\DataProvider('discountScenarioProvider')]
  public function testDiscountScenarios(array $itemsData, float $expectedSubtotal, bool $shouldHaveDiscount): void
  {
    // Arrange
    $cartItems = [];
    $productId = 1;

    foreach ($itemsData as $item) {
      $cartItems[] = $this->createCartItem($productId++, $item['price'], $item['qty']);
    }

    // Act
    $totals = CartTotalsDTO::calculateFromItems($cartItems, 200.0, 0.10, 'EUR');

    // Assert
    $this->assertEquals($expectedSubtotal, $totals->subtotal);
    $this->assertEquals($shouldHaveDiscount, $totals->hasDiscount);

    if ($shouldHaveDiscount) {
      $this->assertGreaterThan(0, $totals->discount);
      $this->assertLessThan($totals->subtotal, $totals->total);
    } else {
      $this->assertEquals(0.0, $totals->discount);
      $this->assertEquals($totals->subtotal, $totals->total);
    }
  }
}
