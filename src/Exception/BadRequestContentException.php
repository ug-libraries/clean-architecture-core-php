<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Exception;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class BadRequestContentException extends Exception
{
    /**
     * @param array<string, mixed> $errors
     */
    public function __construct(array $errors)
    {
        parent::__construct($errors);
    }
}
