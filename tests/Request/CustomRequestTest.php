<?php

/*
 * This file is part of the Cleancoders Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Cleancoders\Tests\Core\Request;

use Cleancoders\Core\Exception\BadRequestContentException;
use Cleancoders\Core\Exception\Exception;
use Cleancoders\Core\Request\Request as BaseRequest;
use Cleancoders\Core\Request\RequestBuilderInterface;
use Cleancoders\Core\Request\RequestInterface;
use Cleancoders\Core\Response\StatusCode;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class CustomRequestTest extends TestCase
{
    private RequestInterface $customRequest;

    protected function setUp(): void
    {
        $this->customRequest = new class () extends BaseRequest implements RequestInterface {
            protected static array $requestPossibleFields = [
                'field_1' => null,
                'field_2' => null,
            ];
        };
    }

    public function testCanBuildNewRequest(): void
    {
        $customRequest = new class () extends BaseRequest implements RequestInterface {
        };
        $this->assertInstanceOf(RequestBuilderInterface::class, $customRequest::createFromPayload([]));
    }

    public function testCanBuildNewRequestAndGetRequestId(): void
    {
        $customRequest = new class () extends BaseRequest implements RequestInterface {
        };
        $createdRequest = $customRequest::createFromPayload([]);
        $this->assertNotNull($createdRequest->getRequestId());
        $this->assertInstanceOf(RequestBuilderInterface::class, $createdRequest);
    }

    public function testCanBuildNewRequestWithParameters(): void
    {
        $this->assertInstanceOf(RequestBuilderInterface::class, $this->customRequest::createFromPayload([
            'field_1' => 1,
            'field_2' => 'value',
        ]));
    }

    public function testCanNotBuildNewRequestWithUnrequiredParameters(): void
    {
        try {
            $this->customRequest::createFromPayload([
                'field_1' => 1,
                'field_2' => 'value',
                'field_3' => new stdClass(),
            ]);
        } catch (BadRequestContentException $exception) {
            $this->errorsFieldAssertion(
                $exception,
                ['field_3'],
                'unrequired_fields',
                'illegal.fields'
            );
        }
    }

    public function testCanNotBuildNewRequestWithUnrequiredNestedParameters(): void
    {
        try {
            $customRequest = new class () extends BaseRequest implements RequestInterface {
                protected static array $requestPossibleFields = [
                    'field_1' => null,
                    'field_2' => null,
                    'field_3' => null,
                    'field_4' => [
                        'field_5' => null,
                    ],
                ];
            };
            $customRequest::createFromPayload([
                'field_1' => 1,
                'field_2' => 'value',
                'field_3' => new stdClass(),
                'field_4' => [
                    'field_5' => 2,
                    'field_6' => 3,
                ],
            ]);
        } catch (BadRequestContentException $exception) {
            $this->errorsFieldAssertion(
                $exception,
                ['field_4.field_6'],
                'unrequired_fields',
                'illegal.fields'
            );
        }
    }

    public function testCanNotBuildNewRequestWithUnrequiredMoreNestedParameters(): void
    {
        try {
            $customRequest = new class () extends BaseRequest implements RequestInterface {
                protected static array $requestPossibleFields = [
                    'field_1' => null,
                    'field_2' => null,
                    'field_3' => null,
                    'field_4' => [
                        'field_5' => [
                            'field_6' => '',
                        ],
                    ],
                ];
            };
            $customRequest::createFromPayload([
                'field_1' => 1,
                'field_2' => 'value',
                'field_3' => new stdClass(),
                'field_4' => [
                    'field_5' => [],
                ],
            ]);
        } catch (BadRequestContentException $exception) {
            $this->errorsFieldAssertion(
                $exception,
                ['field_4.field_5.field_6'],
                'missing_fields',
                'missing.required.fields'
            );
        }
    }

    public function testCanNotBuildNewRequestWithMissingParameters(): void
    {
        try {
            $this->customRequest::createFromPayload([
                'field_1' => 1,
            ]);
        } catch (BadRequestContentException $exception) {
            $this->errorsFieldAssertion(
                $exception,
                ['field_2'],
                'missing_fields',
                'missing.required.fields'
            );
        }
    }

    public function testCanBuildNewRequestWithRequiredParametersAndGetRequestData(): void
    {
        $customRequest = new class () extends BaseRequest implements RequestInterface {
            protected static array $requestPossibleFields = [
                'field_1' => null,
                'field_2' => null,
                'field_3' => null,
            ];
        };
        $payload = [
            'field_1' => 1,
            'field_2' => 'value',
            'field_3' => new stdClass(),
        ];
        $this->assertEquals($payload, $customRequest::createFromPayload($payload)->getRequestData());
    }

    /**
     * @param array<int, string> $fields
     */
    private function errorsFieldAssertion(
        Exception $exception,
        array $fields,
        string $errorKey,
        string $errorMessage
    ): void {
        $this->assertEquals(StatusCode::BAD_REQUEST->getValue(), $exception->getCode());
        $this->assertEquals($errorMessage, $exception->getMessage());
        $this->assertEquals($fields, $exception->getErrors()[$errorKey]);
    }
}
