<?php

/*
 * This file is part of the Cleancoders Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Cleancoders\Core\Response;

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
    public function get(string $key): mixed;
}
