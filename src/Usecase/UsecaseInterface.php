<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Usecase;

use Urichy\Core\Presenter\PresenterInterface;
use Urichy\Core\Request\RequestBuilderInterface;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
interface UsecaseInterface
{
    /**
     * Execute the application request
     */
    public function execute(): void;

    /**
     * Set applicative request to be processed by usecase
     */
    public function setRequest(?RequestBuilderInterface $request = null): static;

    /**
     * Set presenter to get usecase response
     */
    public function setPresenter(?PresenterInterface $presenter = null): static;
}
