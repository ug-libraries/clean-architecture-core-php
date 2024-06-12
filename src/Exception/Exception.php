<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Exception;

use RuntimeException;
use Urichy\Core\Exception\Trait\ExceptionFormatter;
use Urichy\Core\Response\StatusCode;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
abstract class Exception extends RuntimeException implements ExceptionInterface
{
    use ExceptionFormatter;

    /**
     * Exception status code.
     *
     * @var int The status code
     */
    protected int $statusCode = StatusCode::BAD_REQUEST->value;

    /**
     * Custom data into the exception.
     *
     * @var array<string, mixed>
     */
    protected array $errors;

    /**
     * @param array<string, mixed> $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct(
            message: $errors['message'] ?? '',
            code: $this->statusCode
        );
        unset($errors['message']);
        $this->errors = $errors;
    }

    /**
     * Get custom exception errors.
     *
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get custom exception errors details.
     *
     * @return array<string, mixed>
     */
    public function getDetails(): array
    {
        return $this->errors['details'] ?? [];
    }

    /**
     * Get custom exception errors details message.
     */
    public function getDetailsMessage(): string
    {
        return $this->getDetails()['error'] ?? '';
    }

    /**
     * Get exception errors for logs.
     *
     * @return array<string, mixed>
     */
    public function getErrorsForLog(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'errors' => $this->errors,
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'previous' => $this->getPrevious(),
            'trace_as_array' => $this->getTrace(),
            'trace_as_string' => $this->getTraceAsString(),
        ];
    }
}
