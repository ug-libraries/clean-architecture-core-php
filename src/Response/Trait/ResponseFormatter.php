<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Response\Trait;

use Urichy\Core\Enum\Status;

trait ResponseFormatter
{
    /**
     * @return array<string, mixed>
     */
    public function output(): array
    {
        return [
            'status' => $this->status(),
            'code' => $this->getStatusCode(),
            'message' => $this->getMessage(),
        ] + $this->mapDataKeyAccordingToResponseStatus();
    }
    private function status(): string
    {
        return $this->isSuccess() ? Status::SUCCESS->value : Status::ERROR->value;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDataKeyAccordingToResponseStatus(): array
    {
        return $this->isSuccess() ? [
            'data' => $this->getData(),
        ] : [
            'details' => $this->getData(),
        ];
    }
}
