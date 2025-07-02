<?php

namespace App\DTO;

class CartTotalsDTO
{
  public function __construct(
    public readonly float $subtotal,
    public readonly float $discount,
    public readonly float $total,
    public readonly bool $hasDiscount,
    public readonly string $currency = 'EUR'
  ) {}

  public static function calculateFromItems(
    array $cartItems,
    float $discountThreshold,
    float $discountRate,
    string $currency
  ): self {
    $subtotal = array_sum(array_map(fn(CartItemDTO $item) => $item->totalPrice, $cartItems));

    $hasDiscount = $subtotal >= $discountThreshold;
    $discount = $hasDiscount ? $subtotal * $discountRate : 0.0;
    $total = $subtotal - $discount;

    return new self(
      subtotal: round($subtotal, 2),
      discount: round($discount, 2),
      total: round($total, 2),
      hasDiscount: $hasDiscount,
      currency: $currency
    );
  }

  public function toArray(): array
  {
    return [
      'subtotal' => $this->subtotal,
      'discount' => $this->discount,
      'total' => $this->total,
      'hasDiscount' => $this->hasDiscount,
      'currency' => $this->currency
    ];
  }
}
