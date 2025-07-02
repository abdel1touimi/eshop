<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FakeStoreApiService
{
  public function __construct(
    private readonly HttpClientInterface $httpClient,
    private readonly string $apiBaseUrl
  ) {}

  public function getAllProducts(?int $limit = null, ?string $sort = null): array
  {
    try {
      $queryParams = [];

      if ($limit !== null) {
        $queryParams['limit'] = $limit;
      }

      if ($sort !== null && in_array(strtolower($sort), ['asc', 'desc'])) {
        $queryParams['sort'] = strtolower($sort);
      }

      $url = $this->apiBaseUrl . '/products';
      if (!empty($queryParams)) {
        $url .= '?' . http_build_query($queryParams);
      }

      $response = $this->httpClient->request('GET', $url);
      return $response->toArray();
    } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
      throw new \Exception('Failed to fetch products from external API: ' . $e->getMessage());
    }
  }

  public function getProduct(int $id): array
  {
    try {
      $response = $this->httpClient->request('GET', $this->apiBaseUrl . '/products/' . $id);
      return $response->toArray();
    } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
      throw new \Exception('Failed to fetch product from external API: ' . $e->getMessage());
    }
  }

  public function getProductsByCategory(string $category, ?int $limit = null, ?string $sort = null): array
  {
    try {
      $queryParams = [];

      if ($limit !== null) {
        $queryParams['limit'] = $limit;
      }

      if ($sort !== null && in_array(strtolower($sort), ['asc', 'desc'])) {
        $queryParams['sort'] = strtolower($sort);
      }

      $url = $this->apiBaseUrl . '/products/category/' . urlencode($category);
      if (!empty($queryParams)) {
        $url .= '?' . http_build_query($queryParams);
      }

      $response = $this->httpClient->request('GET', $url);
      return $response->toArray();
    } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
      throw new \Exception('Failed to fetch products by category from external API: ' . $e->getMessage());
    }
  }

  public function getCategories(): array
  {
    try {
      $response = $this->httpClient->request('GET', $this->apiBaseUrl . '/products/categories');
      return $response->toArray();
    } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
      throw new \Exception('Failed to fetch categories from external API: ' . $e->getMessage());
    }
  }
}
