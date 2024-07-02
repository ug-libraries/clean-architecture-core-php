<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Presenter;

use Urichy\Core\Response\ResponseInterface;

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
}
