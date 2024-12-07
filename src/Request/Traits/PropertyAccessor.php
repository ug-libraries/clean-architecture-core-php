<?php

declare(strict_types=1);

namespace Urichy\Core\Request\Traits;

trait PropertyAccessor
{
    /**
     * @var array<string, mixed>
     */
    private array $requestPayload = [];

    public function __set(string $name, mixed $value): void
    {
        $this->requestPayload[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }
    public function get(string $fieldName, mixed $default = null): mixed
    {
        $value = $this->requestPayload;
        foreach (explode('.', $fieldName) as $field) {
            if (is_array($value)) {
                if (!isset($value[$field])) {
                    $value = $default;
                    break;
                }
                $value = $value[$field];
            } elseif (is_object($value)) {
                if (!property_exists($value, $field)) {
                    $value = $default;
                    break;
                }
                $value = $value->{$field};
            } else {
                $value = $default;
                break;
            }
        }

        return $value;
    }
}
