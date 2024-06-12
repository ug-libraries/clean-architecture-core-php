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
interface ExceptionInterface extends \Throwable
{
    /**
     * Format exception as array.
     *
     * @return array<string, mixed>
     */
    public function format(): array;

    /**
     * Get custom exception errors.
     *
     * @return array<string, mixed>
     */
    public function getErrors(): array;

    /**
     * Get custom exception errors details.
     *
     * @return array<string, mixed>
     */
    public function getDetails(): array;

    /**
     * Get custom exception errors details message.
     */
    public function getDetailsMessage(): string;

    /**
     * Get exception errors for logs.
     *
     * @return array<string, mixed>
     */
    public function getErrorsForLog(): array;
}
