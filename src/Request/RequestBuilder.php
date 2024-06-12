<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Core\Request;

use Ramsey\Uuid\Uuid;
use Urichy\Core\Request\Traits\RequestTransformer;

/**
 * Build an application request
 *
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class RequestBuilder implements RequestBuilderInterface
{
    use RequestTransformer;

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
     * Get application request data as object.
     */
    public function getRequestDataAsObject(): object
    {
        return self::requestToObject($this->toArray());
    }

    /**
     * Get application request data as array.
     *
     * @return array<string, mixed>
     */
    public function getRequestDataAsArray(): array
    {
        return $this->toArray();
    }

    /**
     * Get application request uniq id.
     */
    public function getRequestId(): string
    {
        return $this->requestId;
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(): array
    {
        $data = [];
        foreach ($this->requestParams as $param) {
            $data[$param->getField()] = $param->getValue();
        }

        return $data;
    }
}
