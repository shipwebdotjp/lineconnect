<?php

namespace Shipweb\LineConnect\Interaction;

/**
 * Represents the result of a validation check.
 */
class ValidationResult {
    /**
     * Array of error messages. Empty array means valid.
     *
     * @var string[]
     */
    private array $errors;

    private function __construct(array $errors = []) {
        $this->errors = $errors;
    }

    /**
     * Creates a success result (no errors).
     *
     * @return self
     */
    public static function success(): self {
        return new self([]);
    }

    /**
     * Creates a failure result with a single error message.
     *
     * @param string $message
     * @return self
     */
    public static function failure(string $message): self {
        return new self([$message]);
    }

    /**
     * Creates a failure result from multiple error messages.
     *
     * @param string[] $errors
     * @return self
     */
    public static function fromErrors(array $errors): self {
        return new self(array_values($errors));
    }

    /**
     * Returns true when there are no errors.
     *
     * @return bool
     */
    public function isValid(): bool {
        return count($this->errors) === 0;
    }

    /**
     * Returns all error messages.
     *
     * @return string[]
     */
    public function getErrors(): array {
        return $this->errors;
    }
}
