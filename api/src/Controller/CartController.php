<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\ValidationService;
use App\DTO\Request\AddToCartRequest;
use App\DTO\Request\UpdateCartItemRequest;
use App\DTO\Response\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/api/cart')]
class CartController extends AbstractController
{
  public function __construct(
    private readonly CartService $cartService,
    private readonly ValidationService $validationService,
    private readonly LoggerInterface $logger
  ) {}

  #[Route('', methods: ['GET'])]
  public function getCart(): JsonResponse
  {
    try {
      $this->logger->info('Retrieving cart');

      $cart = $this->cartService->getCart();

      $this->logger->info('Cart retrieved successfully', [
        'items_count' => count($cart->items),
        'total' => $cart->totals->total
      ]);

      $response = ApiResponse::success(
        data: $cart->toArray(),
        message: 'Cart retrieved successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to retrieve cart', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Please try again later',
        statusCode: 500,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/add', methods: ['POST'])]
  public function addToCart(Request $request): JsonResponse
  {
    try {
      $this->logger->info('Adding product to cart');

      $data = json_decode($request->getContent(), true);

      if (!is_array($data)) {
        $this->logger->warning('Invalid JSON payload received');

        $response = ApiResponse::error(
          message: 'Invalid JSON payload',
          statusCode: 400
        );

        return $this->json($response->toArray(), $response->statusCode);
      }

      $addRequest = AddToCartRequest::fromArray($data);
      $validationErrors = $this->validationService->validate($addRequest);

      if (!empty($validationErrors)) {
        $this->logger->warning('Add to cart validation failed', [
          'errors' => $validationErrors,
          'data' => $data
        ]);

        $response = ApiResponse::validationError(
          errors: $validationErrors,
          message: 'Validation failed'
        );

        return $this->json($response->toArray(), $response->statusCode);
      }

      $cart = $this->cartService->addToCart($addRequest->productId, $addRequest->quantity);

      $this->logger->info('Product added to cart successfully', [
        'productId' => $addRequest->productId,
        'quantity' => $addRequest->quantity,
        'cart_total' => $cart->totals->total,
        'items_count' => count($cart->items)
      ]);

      $response = ApiResponse::success(
        data: $cart->toArray(),
        message: 'Product added to cart successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to add product to cart', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'request_data' => $data ?? null
      ]);

      $response = ApiResponse::error(
        message: 'Failed to add product to cart',
        statusCode: 400,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/item/{productId}', methods: ['PUT'], requirements: ['productId' => '\d+'])]
  public function updateCartItem(int $productId, Request $request): JsonResponse
  {
    try {
      $this->logger->info('Updating cart item', ['productId' => $productId]);

      $data = json_decode($request->getContent(), true);

      if (!is_array($data)) {
        $this->logger->warning('Invalid JSON payload for cart update', ['productId' => $productId]);

        $response = ApiResponse::error(
          message: 'Invalid JSON payload',
          statusCode: 400
        );

        return $this->json($response->toArray(), $response->statusCode);
      }

      $updateRequest = UpdateCartItemRequest::fromArray($data);
      $validationErrors = $this->validationService->validate($updateRequest);

      if (!empty($validationErrors)) {
        $this->logger->warning('Update cart item validation failed', [
          'productId' => $productId,
          'errors' => $validationErrors,
          'data' => $data
        ]);

        $response = ApiResponse::validationError(
          errors: $validationErrors,
          message: 'Validation failed'
        );

        return $this->json($response->toArray(), $response->statusCode);
      }

      $cart = $this->cartService->updateCartItem($productId, $updateRequest->quantity);

      $this->logger->info('Cart item updated successfully', [
        'productId' => $productId,
        'new_quantity' => $updateRequest->quantity,
        'cart_total' => $cart->totals->total
      ]);

      $response = ApiResponse::success(
        data: $cart->toArray(),
        message: 'Cart item updated successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to update cart item', [
        'productId' => $productId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'request_data' => $data ?? null
      ]);

      $response = ApiResponse::error(
        message: 'Failed to update cart item',
        statusCode: 400,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/item/{productId}', methods: ['DELETE'], requirements: ['productId' => '\d+'])]
  public function removeFromCart(int $productId): JsonResponse
  {
    try {
      $this->logger->info('Removing product from cart', ['productId' => $productId]);

      $cart = $this->cartService->removeFromCart($productId);

      $this->logger->info('Product removed from cart successfully', [
        'productId' => $productId,
        'remaining_items' => count($cart->items),
        'cart_total' => $cart->totals->total
      ]);

      $response = ApiResponse::success(
        data: $cart->toArray(),
        message: 'Product removed from cart successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to remove product from cart', [
        'productId' => $productId,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Failed to remove product from cart',
        statusCode: 500,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/clear', methods: ['DELETE'])]
  public function clearCart(): JsonResponse
  {
    try {
      $this->logger->info('Clearing cart');

      $cart = $this->cartService->clearCart();

      $this->logger->info('Cart cleared successfully');

      $response = ApiResponse::success(
        data: $cart->toArray(),
        message: 'Cart cleared successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to clear cart', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Failed to clear cart',
        statusCode: 500,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }

  #[Route('/count', methods: ['GET'])]
  public function getCartCount(): JsonResponse
  {
    try {
      $this->logger->info('Getting cart count');

      $count = $this->cartService->getCartItemsCount();

      $this->logger->info('Cart count retrieved', ['count' => $count]);

      $response = ApiResponse::success(
        data: ['count' => $count],
        message: 'Cart count retrieved successfully'
      );

      return $this->json($response->toArray(), $response->statusCode);
    } catch (\Exception $e) {
      $this->logger->error('Failed to get cart count', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      $response = ApiResponse::error(
        message: 'Please try again later',
        statusCode: 500,
        error: $e->getMessage()
      );

      return $this->json($response->toArray(), $response->statusCode);
    }
  }
}
