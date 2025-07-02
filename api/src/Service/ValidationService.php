<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Psr\Log\LoggerInterface;

class ValidationService
{
  public function __construct(
    private readonly ValidatorInterface $validator,
    private readonly LoggerInterface $logger
  ) {}

  /**
   * Validate an object and return formatted errors
   */
  public function validate(object $object): array
  {
    $violations = $this->validator->validate($object);

    if (count($violations) > 0) {
      $this->logger->warning('Validation failed', [
        'object' => get_class($object),
        'violations_count' => count($violations),
        'violations' => $this->formatViolations($violations)
      ]);
    }

    return $this->formatViolations($violations);
  }

  /**
   * Check if object is valid
   */
  public function isValid(object $object): bool
  {
    return count($this->validator->validate($object)) === 0;
  }

  /**
   * Format constraint violations into array
   */
  private function formatViolations(ConstraintViolationListInterface $violations): array
  {
    $errors = [];

    foreach ($violations as $violation) {
      $errors[] = [
        'field' => $violation->getPropertyPath(),
        'message' => $violation->getMessage(),
        'value' => $violation->getInvalidValue()
      ];
    }

    return $errors;
  }
}
