<?php

namespace App\Service;

use App\DTO\CartDTO;
use App\DTO\CartItemDTO;
use App\DTO\CartTotalsDTO;
use Symfony\Component\HttpFoundation\RequestStack;

namespace App\Service;

use App\DTO\CartDTO;
use App\DTO\CartItemDTO;
use App\DTO\CartTotalsDTO;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
  public function __construct(
    private readonly ProductService $productService,
    private readonly RequestStack $requestStack,
    private readonly string $sessionKey,
    private readonly float $discountThreshold,
    private readonly float $discountRate,
    private readonly string $defaultCurrency
  ) {}

  public function getCart(): CartDTO
  {
    $session = $this->requestStack->getSession();
    $cartData = $session->get($this->sessionKey, []);

    if (empty($cartData)) {
      return new CartDTO([], new CartTotalsDTO(0, 0, 0, false, $this->defaultCurrency));
    }

    $cartItems = [];
    foreach ($cartData as $productId => $sessionItem) {
      try {
        $product = $this->productService->getProduct($productId);
        $cartItems[] = CartItemDTO::fromSessionData($sessionItem, $product);
      } catch (\Exception $e) {
        $this->removeFromCart($productId);
      }
    }

    $totals = CartTotalsDTO::calculateFromItems(
      $cartItems,
      $this->discountThreshold,
      $this->discountRate,
      $this->defaultCurrency
    );

    return new CartDTO($cartItems, $totals);
  }

  public function addToCart(int $productId, int $quantity = 1): CartDTO
  {
    $this->productService->getProduct($productId);

    $session = $this->requestStack->getSession();
    $cartData = $session->get($this->sessionKey, []);

    if (isset($cartData[$productId])) {
      $cartData[$productId]['quantity'] += $quantity;
    } else {
      $cartData[$productId] = ['quantity' => $quantity];
    }

    $session->set($this->sessionKey, $cartData);

    return $this->getCart();
  }

  public function updateCartItem(int $productId, int $quantity): CartDTO
  {
    if ($quantity <= 0) {
      return $this->removeFromCart($productId);
    }

    $session = $this->requestStack->getSession();
    $cartData = $session->get($this->sessionKey, []);

    if (!isset($cartData[$productId])) {
      throw new \Exception('Product not found in cart');
    }

    $cartData[$productId]['quantity'] = $quantity;
    $session->set($this->sessionKey, $cartData);

    return $this->getCart();
  }

  public function removeFromCart(int $productId): CartDTO
  {
    $session = $this->requestStack->getSession();
    $cartData = $session->get($this->sessionKey, []);

    if (isset($cartData[$productId])) {
      unset($cartData[$productId]);
      $session->set($this->sessionKey, $cartData);
    }

    return $this->getCart();
  }

  public function clearCart(): CartDTO
  {
    $session = $this->requestStack->getSession();
    $session->remove($this->sessionKey);

    return $this->getCart();
  }

  public function getCartItemsCount(): int
  {
    $session = $this->requestStack->getSession();
    $cartData = $session->get($this->sessionKey, []);

    return array_sum(array_column($cartData, 'quantity'));
  }
}
