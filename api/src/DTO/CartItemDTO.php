<?php

namespace App\DTO;

class CartItemDTO
{
  public function __construct(
    public readonly int $productId,
    public readonly string $title,
    public readonly float $price,
    public readonly string $currency,
    public readonly int $quantity,
    public readonly string $image,
    public readonly float $totalPrice
  ) {}

  public static function fromSessionData(array $sessionItem, ProductDTO $product): self
  {
    $quantity = $sessionItem['quantity'];
    $totalPrice = $product->price * $quantity;

    return new self(
      productId: $product->id,
      title: $product->title,
      price: $product->price,
      currency: $product->currency,
      quantity: $quantity,
      image: $product->image,
      totalPrice: $totalPrice
    );
  }

  public function toArray(): array
  {
    return [
      'productId' => $this->productId,
      'title' => $this->title,
      'price' => $this->price,
      'currency' => $this->currency,
      'quantity' => $this->quantity,
      'image' => $this->image,
      'totalPrice' => $this->totalPrice
    ];
  }
}
