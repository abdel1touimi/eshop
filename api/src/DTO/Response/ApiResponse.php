<?php

namespace App\DTO\Response;

class ApiResponse
{
  public function __construct(
    public readonly bool $success,
    public readonly mixed $data = null,
    public readonly string $message = '',
    public readonly ?string $error = null,
    public readonly ?array $errors = null,
    public readonly int $statusCode = 200
  ) {}

  public static function success(mixed $data = null, string $message = 'Success'): self
  {
    return new self(
      success: true,
      data: $data,
      message: $message,
      statusCode: 200
    );
  }

  public static function error(string $message = 'Error', int $statusCode = 400, ?string $error = null): self
  {
    return new self(
      success: false,
      message: $message,
      error: $error,
      statusCode: $statusCode
    );
  }

  public static function validationError(array $errors, string $message = 'Validation failed'): self
  {
    return new self(
      success: false,
      message: $message,
      errors: $errors,
      statusCode: 422
    );
  }

  public function toArray(): array
  {
    $response = [
      'success' => $this->success,
      'message' => $this->message
    ];

    if ($this->data !== null) {
      $response['data'] = $this->data;
    }

    if ($this->error !== null) {
      $response['error'] = $this->error;
    }

    if ($this->errors !== null) {
      $response['errors'] = $this->errors;
    }

    return $response;
  }
}
