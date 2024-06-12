<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Request;

use Throwable;
use Urichy\Core\Exception\BadRequestContentException;
use Urichy\Core\Request\Traits\RequestFilter;

/**
 * Application request
 *
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
abstract class Request implements RequestInterface
{
    use RequestFilter;

    /**
     * Creates a new application request from given payload.
     *
     * @param array<string, mixed> $payload
     * @throws BadRequestContentException
     */
    public static function createFromPayload(array $payload): RequestBuilderInterface
    {
        $requestValidationResult = static::requestPayloadFilter($payload);
        static::throwMissingFieldsExceptionIfNeeded($requestValidationResult['missing_fields']);
        static::throwUnRequiredFieldsExceptionIfNeeded($requestValidationResult['unauthorized_fields']);

        try {
            static::applyConstraintsOnRequestFields($payload);
        } catch (Throwable $exception) {
            throw new BadRequestContentException([
                'message' => 'invalid.request.fields',
                'details' => [
                    'error' => $exception->getMessage(),
                ],
            ]);
        }

        return new RequestBuilder($payload);
    }

    /**
     * Throws an error if the request has missing fields.
     *
     * @param array<int|string, string> $missingFields
     */
    protected static function throwMissingFieldsExceptionIfNeeded(array $missingFields): void
    {
        if (count($missingFields) > 0) {
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
        if (count($unauthorizedFields) > 0) {
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
