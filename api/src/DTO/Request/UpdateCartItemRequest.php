<?php

namespace App\DTO\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateCartItemRequest
{
  #[Assert\NotBlank(message: 'Quantity is required')]
  #[Assert\Type(type: 'integer', message: 'Quantity must be an integer')]
  #[Assert\Range(
    min: 0,
    max: 99,
    notInRangeMessage: 'Quantity must be between {{ min }} and {{ max }}'
  )]
  public int $quantity;

  public static function fromArray(array $data): self
  {
    $request = new self();
    $request->quantity = (int) ($data['quantity'] ?? 0);

    return $request;
  }

  public function toArray(): array
  {
    return [
      'quantity' => $this->quantity
    ];
  }
}
