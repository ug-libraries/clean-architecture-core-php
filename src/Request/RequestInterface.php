<?php

/*
 * This file is part of the Cleancoders Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Cleancoders\Core\Request;

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
    public static function createFromPayload(array $payload): RequestBuilderInterface;
}
