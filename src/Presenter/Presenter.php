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
abstract class Presenter implements PresenterInterface
{
    /**
     * The application response
     */
    protected ResponseInterface $response;

    /**
     * Present use case response to the client
     */
    public function present(ResponseInterface $response): void
    {
        $this->response = $response;
    }

    /**
     * Get the use case response
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @return array<string, mixed>
     */
    public function getFormattedResponse(): array
    {
        return $this->response->output();
    }
}
