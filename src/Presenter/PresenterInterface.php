<?php

/*
 * This file is part of the Cleancoders Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Cleancoders\Core\Presenter;

use Cleancoders\Core\Http\Response\ResponseInterface;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
interface PresenterInterface
{
    /**
     * Use by use case to send application response.
     *
     * @param ResponseInterface $response The response to be presented.
     */
    public function present(ResponseInterface $response): void;

    /**
     * Get the use case response that was sent.
     *
     * @return ResponseInterface The response that was sent.
     */
    public function getResponse(): ResponseInterface;
}
