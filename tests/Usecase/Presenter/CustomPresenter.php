<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Tests\Core\Usecase\Presenter;

use Urichy\Core\Presenter\Presenter as BasePresenter;
use Urichy\Core\Response\ResponseInterface;

final class CustomPresenter extends BasePresenter
{
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
