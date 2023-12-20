<?php

/*
 * This file is part of the Cleancoders Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Cleancoders\Core\Request;

/**
 * Create a new request parameter.
 *
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class RequestParam
{
    public function __construct(
        private readonly string $field,
        private readonly mixed $value
    ) {
    }

    /**
     * Get request parameter name.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Get request parameter value.
     */
    public function getValue(): mixed
    {
        return $this->value;
    }
}
