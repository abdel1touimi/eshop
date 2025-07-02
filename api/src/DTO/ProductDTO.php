<?php

namespace App\DTO;

class ProductDTO
{
  public function __construct(
    public readonly int $id,
    public readonly string $title,
    public readonly float $price,
    public readonly string $currency,
    public readonly string $description,
    public readonly string $category,
    public readonly string $image,
    public readonly ?array $rating = null
  ) {}

  public static function fromFakeStoreApi(array $data, string $currency = 'EUR'): self
  {
    return new self(
      id: $data['id'],
      title: $data['title'],
      price: (float) $data['price'],
      currency: $currency,
      description: $data['description'],
      category: $data['category'],
      image: $data['image'],
      rating: $data['rating'] ?? null
    );
  }

  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'title' => $this->title,
      'price' => $this->price,
      'currency' => $this->currency,
      'description' => $this->description,
      'category' => $this->category,
      'image' => $this->image,
      'rating' => $this->rating
    ];
  }
}
