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

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
abstract class Usecase implements UsecaseInterface
{
    /**
     * The application request to be processed by usecase
     */
    protected RequestInterface $request;

    /**
     * The presenter to which the usecase processing response will be sent
     */
    protected PresenterInterface $presenter;

    /**
     * Set applicative request to be processed by usecase
     */
    public function setRequest(RequestInterface $request): static
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set presenter to get usecase response
     */
    public function setPresenter(PresenterInterface $presenter): static
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
     * Get the application request field value.
     */
    protected function getField(string $name, mixed $default = null): mixed
    {
        return $this->request->get($name, $default);
    }
}
