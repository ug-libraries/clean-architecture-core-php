<?php

declare(strict_types=1);

namespace Urichy\Core\Request\Traits;

trait RequestTransformer
{
    use PropertyAccessor;

    /**
     * @param array<string, mixed> $requestPayload
     */
    protected static function requestToObject(array $requestPayload): static
    {
        $instance = new static();

        foreach ($requestPayload as $field => $value) {
            $instance->{$field} = $value;
        }

        return $instance;
    }
}
