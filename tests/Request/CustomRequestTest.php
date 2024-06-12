<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Tests\Core\Request;

use Assert\Assert;
use PHPUnit\Framework\TestCase;
use stdClass;
use Urichy\Core\Exception\BadRequestContentException;
use Urichy\Core\Exception\Exception;
use Urichy\Core\Request\Request as BaseRequest;
use Urichy\Core\Request\RequestBuilderInterface;
use Urichy\Core\Request\RequestInterface;
use Urichy\Core\Response\StatusCode;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class CustomRequestTest extends TestCase
{
    private RequestInterface $customRequest;

    protected function setUp(): void
    {
        $this->customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => true,
                'field_2' => true,
            ];
        };
    }

    public function testCanBuildNewRequestWithoutParameters(): void
    {
        $customRequest = new class () extends BaseRequest {
        };
        $this->assertInstanceOf(RequestBuilderInterface::class, $customRequest::createFromPayload([]));
    }

    public function testCanBuildNewRequestAndGetRequestId(): void
    {
        $customRequest = new class () extends BaseRequest {
        };
        $createdRequest = $customRequest::createFromPayload([]);
        $this->assertNotNull($createdRequest->getRequestId());
        $this->assertInstanceOf(RequestBuilderInterface::class, $createdRequest);
    }

    public function testCanBuildNewRequestWithParameters(): void
    {
        $this->assertInstanceOf(RequestBuilderInterface::class, $this->customRequest::createFromPayload([
            'field_1' => true,
            'field_2' => true,
        ]));
    }

    public function testCanNotBuildNewRequestWithMissingParameters(): void
    {
        $customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => true,
                'field_2' => false,
                'field_3' => true,
                'field_4' => [
                    'field_5' => false,
                    'field_6' => true,
                ],
            ];
        };

        try {
            $customRequest::createFromPayload([
                'field_1' => 1,
                'field_3' => new stdClass(),
                'field_4' => [
                    'field_5' => 2,
                ],
            ]);
        } catch (BadRequestContentException $exception) {
            $this->errorsFieldAssertion(
                $exception,
                [
                    'field_4.field_6' => 'required',
                ],
                'missing_fields',
                'missing.required.fields'
            );
        }
    }

    public function testCanNotBuildNewRequestWithWrongNestedMissingParameters(): void
    {
        $customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => true,
                'field_2' => false,
                'field_3' => true,
                'field_4' => [
                    'field_5' => false,
                    'field_6' => true,
                ],
            ];
        };

        try {
            $customRequest::createFromPayload([
                'field_1' => 1,
                'field_3' => new stdClass(),
                'field_4' => 1,
            ]);
        } catch (BadRequestContentException $exception) {
            $this->errorsFieldAssertion(
                $exception,
                [
                    'field_4' => 'required field type not matching array',
                ],
                'missing_fields',
                'missing.required.fields'
            );
        }
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
        $customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
                'field_4' => [
                    'field_5' => true,
                ],
            ];
        };

        try {
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

    public function testCanNotBuildNewRequestWithMissingMoreNestedParameters(): void
    {
        $customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
                'field_4' => [
                    'field_5' => [
                        'field_6' => true,
                    ],
                ],
            ];
        };

        try {
            $customRequest::createFromPayload([
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
                'field_4' => [
                    'field_5' => [],
                ],
            ]);
        } catch (BadRequestContentException $exception) {
            $this->errorsFieldAssertion(
                $exception,
                [
                    'field_4.field_5.field_6' => 'required',
                ],
                'missing_fields',
                'missing.required.fields'
            );
        }
    }

    public function testCanBuildNewRequestWithRequiredParametersAndGetRequestDataAsArray(): void
    {
        $customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
            ];
        };
        $payload = [
            'field_1' => 1,
            'field_2' => 'value',
            'field_3' => new stdClass(),
        ];
        $this->assertEquals($payload, $customRequest::createFromPayload($payload)->getRequestDataAsArray());
    }

    public function testCanBuildNewRequestWithRequiredParametersAndGetRequestDataAsObject(): void
    {
        $customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
            ];
        };
        $payload = [
            'field_1' => 1,
            'field_2' => 'value',
            'field_3' => new stdClass(),
        ];

        $this->assertEquals(
            json_decode(json_encode([
                'field_1' => 1,
                'field_2' => 'value',
                'field_3' => new stdClass(),
            ])),
            $customRequest::createFromPayload($payload)->getRequestDataAsObject()
        );
    }

    public function testCanBuildNewRequestWithRequiredParametersAndApplyCustomConstraints(): void
    {
        $customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => true,
                'field_2' => true,
                'field_3' => true,
            ];

            protected static function applyConstraintsOnRequestFields(array $requestData): void
            {
                Assert::that(
                    $requestData['field_1'],
                    '[field_1] field must not be an empty string.'
                )->notEmpty()->string();
            }
        };

        try {
            $payload = [
                'field_1' => '',
                'field_2' => 'value',
                'field_3' => new stdClass(),
            ];
            $customRequest::createFromPayload($payload);
        } catch (BadRequestContentException $exception) {
            $this->assertEquals('[field_1] field must not be an empty string.', $exception->getDetailsMessage());
        }
    }

    /**
     * @param array<int|string, string> $fields
     */
    private function errorsFieldAssertion(
        Exception $exception,
        array $fields,
        string $errorKey,
        string $errorMessage
    ): void {
        $this->assertEquals(StatusCode::BAD_REQUEST->getValue(), $exception->getCode());
        $this->assertEquals($errorMessage, $exception->getMessage());
        $this->assertEquals($fields, $exception->getDetails()[$errorKey]);
    }
}
