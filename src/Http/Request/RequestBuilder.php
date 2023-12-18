<?php

/*
 * This file is part of the Cleancoders Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Cleancoders\Core\Http\Request;

use Ramsey\Uuid\Uuid;

/**
 * Build an application request
 *
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class RequestBuilder implements RequestBuilderInterface
{
    /**
     * Application request uniq id
     */
    private string $requestId;

    /**
     * Request parameters
     *
     * @var array<int, RequestParam>
     */
    private array $requestParams = [];

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(array $payload)
    {
        $this->requestId = Uuid::uuid4()->toString();

        foreach ($payload as $field => $value) {
            $this->requestParams[] = new RequestParam(
                field: $field,
                value: $value
            );
        }
    }

    /**
     * Get application request data.
     *
     * @return array<string, mixed>
     */
    public function getRequestData(): array
    {
        $data = [];
        foreach ($this->requestParams as $param) {
            $data[$param->getField()] = $param->getValue();
        }

        return $data;
    }

    /**
     * Get application request uniq id.
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }
}
