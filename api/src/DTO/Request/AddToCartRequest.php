<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class AddToCartRequest
{
  #[Assert\NotBlank(message: 'Product ID is required')]
  #[Assert\Type(type: 'integer', message: 'Product ID must be an integer')]
  #[Assert\Positive(message: 'Product ID must be positive')]
  public int $productId;

  #[Assert\Type(type: 'integer', message: 'Quantity must be an integer')]
  #[Assert\Range(
    min: 1,
    max: 99,
    notInRangeMessage: 'Quantity must be between {{ min }} and {{ max }}'
  )]
  public int $quantity = 1;

  public static function fromArray(array $data): self
  {
    $request = new self();
    $request->productId = (int) ($data['productId'] ?? 0);
    $request->quantity = (int) ($data['quantity'] ?? 1);

    return $request;
  }

  public function toArray(): array
  {
    return [
      'productId' => $this->productId,
      'quantity' => $this->quantity
    ];
  }
}
