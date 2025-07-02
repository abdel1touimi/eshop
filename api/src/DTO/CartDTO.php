<?php

namespace App\DTO;

class CartDTO
{
  public function __construct(
    public readonly array $items,
    public readonly CartTotalsDTO $totals
  ) {}

  public function toArray(): array
  {
    return [
      'items' => array_map(fn(CartItemDTO $item) => $item->toArray(), $this->items),
      'totals' => $this->totals->toArray()
    ];
  }
}
