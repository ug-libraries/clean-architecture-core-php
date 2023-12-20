<?php

/*
 * This file is part of the Cleancoders Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Cleancoders\Core\Usecase;

use Cleancoders\Core\Presenter\PresenterInterface;
use Cleancoders\Core\Request\RequestBuilderInterface;

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
