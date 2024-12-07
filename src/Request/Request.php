<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Request;

use Ramsey\Uuid\Uuid;
use Urichy\Core\Exception\BadRequestContentException;
use Urichy\Core\Request\Traits\RequestFilter;
use Urichy\Core\Request\Traits\RequestTransformer;

/**
 * Application request
 *
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
abstract class Request implements RequestInterface
{
    use RequestFilter;
    use RequestTransformer;

    /**
     * Application request uniq id
     */
    private string $requestId;

    /**
     * Application request payload.
     *
     * @var array<string, mixed>
     */
    private array $requestPayload = [];

    public function __construct()
    {
        $this->requestId = Uuid::uuid4()->toString();
    }

    /**
     * Creates a new application request from given payload.
     *
     * @param array<string, mixed> $payload
     * @throws BadRequestContentException
     */
    public static function createFromPayload(array $payload): static
    {
        $requestValidationResult = static::requestPayloadFilter($payload);
        static::throwMissingFieldsExceptionIfNeeded($requestValidationResult['missing_fields']);
        static::throwUnRequiredFieldsExceptionIfNeeded($requestValidationResult['unauthorized_fields']);

        static::applyConstraintsOnRequestFields($payload);

        return self::requestToObject($payload);
    }

    /**
     * Get application request uniq id.
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * Set application request payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->requestPayload;
    }

    /**
     * Throws an error if the request has missing fields.
     *
     * @param array<int|string, string> $missingFields
     */
    protected static function throwMissingFieldsExceptionIfNeeded(array $missingFields): void
    {
        if (!empty($missingFields)) {
            throw new BadRequestContentException([
                'message' => 'missing.required.fields',
                'details' => [
                    'missing_fields' => $missingFields,
                ],
            ]);
        }
    }

    /**
     * Throws an error if the request has unauthorized fields.
     *
     * @param array<int|string, string> $unauthorizedFields
     */
    protected static function throwUnRequiredFieldsExceptionIfNeeded(array $unauthorizedFields): void
    {
        if (!empty($unauthorizedFields)) {
            throw new BadRequestContentException([
                'message' => 'illegal.fields',
                'details' => [
                    'unrequired_fields' => $unauthorizedFields,
                ],
            ]);
        }
    }

    /**
     * Apply constraints on request fields if needed.
     *
     * @param array<string, mixed> $requestData
     */
    protected static function applyConstraintsOnRequestFields(array $requestData): void
    {
    }
}
