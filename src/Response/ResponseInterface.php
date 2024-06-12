<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Response;

use Urichy\Tests\Core\Response\ResponseTest;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
interface ResponseInterface
{
    /**
     * Check if response is success
     */
    public function isSuccess(): bool;

    /**
     * Get response status code
     */
    public function getStatusCode(): int;

    /**
     * Get custom response message
     */
    public function getMessage(): ?string;

    /**
     * Get the response data
     *
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * Get specific field from response
     */
    public function get(string $fieldName): mixed;

    /**
     * Return response as array with more context
     *
     * @see ResponseTest
     * ex: [
     *     'status'   => 'success',
     *      'code'    => 200,
     *      'message' => null,
     *      'data'    => [
     *          'key' => 'value',
     *      ]
     * ]
     * @return array<string, mixed>
     */
    public function output(): array;
}
