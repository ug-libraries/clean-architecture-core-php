<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Enum;

enum Status: string
{
    case SUCCESS = 'success';
    case ERROR = 'error';
}
