<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Request;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
interface RequestInterface
{
    /**
     * Create a new application request from payload.
     *
     * @param array<string, mixed> $payload
     */
    public static function createFromPayload(array $payload): static;

    /**
     * Get the application request field value.
     */
    public function get(string $fieldName, mixed $default = null): mixed;

    /**
     * Get application request uniq id.
     */
    public function getRequestId(): string;

    /**
     * Set application request payload.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
