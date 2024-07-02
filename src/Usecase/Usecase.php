<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Usecase;

use Urichy\Core\Presenter\PresenterInterface;
use Urichy\Core\Request\RequestInterface;
use Urichy\Core\Response\ResponseInterface;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
abstract class Usecase implements UsecaseInterface
{
    /**
     * The application request to be processed by usecase
     */
    private RequestInterface $request;

    /**
     * The presenter to which the usecase processing response will be sent
     */
    private PresenterInterface $presenter;

    /**
     * Set applicative request to be processed by usecase
     */
    public function withRequest(RequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set presenter to get usecase response
     */
    public function withPresenter(PresenterInterface $presenter): static
    {
        $this->presenter = $presenter;

        return $this;
    }

    /**
     * Get request data
     *
     * @return array<string, mixed>
     */
    protected function getRequestData(): array
    {
        return $this->request->toArray();
    }

    /**
     * Get request data
     */
    protected function getRequestId(): string
    {
        return $this->request->getRequestId();
    }

    /**
     * Get the application request field value.
     */
    protected function getField(string $name, mixed $default = null): mixed
    {
        return $this->request->get($name, $default);
    }

    /**
     * Transport given response to infrastructure layer.
     */
    protected function presentResponse(ResponseInterface $response): void
    {
        $this->presenter->present($response);
    }
}
