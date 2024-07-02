<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Tests\Core\Usecase;

use PHPUnit\Framework\TestCase;
use Urichy\Core\Enum\Status;
use Urichy\Core\Exception\BadRequestContentException;
use Urichy\Core\Request\Request as BaseRequest;
use Urichy\Core\Request\RequestInterface;
use Urichy\Core\Response\Response;
use Urichy\Core\Response\StatusCode;
use Urichy\Core\Usecase\Usecase as BaseUsecase;
use Urichy\Tests\Core\Usecase\Presenter\CustomPresenter;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class UsecaseTest extends TestCase
{
    private RequestInterface $customRequest;
    private CustomPresenter $customPresenter;

    protected function setUp(): void
    {
        $this->customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => null,
                'field_2' => null,
            ];
        };

        $this->customPresenter = new CustomPresenter();
    }

    public function testCanExecuteUsecaseAndReturnEmptyResponse(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                $this->presentResponse(Response::create());
            }
        };

        $usecase
            ->withRequest($this->customRequest::createFromPayload([
                'field_1' => 'field_value',
                'field_2' => [
                    'boolean' => true,
                    'integer' => 666,
                    'float' => 666.666,
                ],
            ]))
            ->withPresenter($this->customPresenter)
            ->execute();

        $response = $this->customPresenter->getResponse();

        $this->assertNotNull($response);
        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->getMessage());
        $this->assertSame(StatusCode::NO_CONTENT->getValue(), $response->getStatusCode());
    }

    public function testCanExecuteUsecaseWithoutRequest(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                $this->presentResponse(Response::create(
                    statusCode: StatusCode::OK->getValue(),
                    data: [
                        'request_data' => [],
                    ]
                ));
            }
        };

        $usecase
            ->withPresenter($this->customPresenter)
            ->execute();

        $response = $this->customPresenter->getResponse();
        $this->assertCount(0, $response->get('request_data'));
        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->getMessage());
        $this->assertSame(StatusCode::OK->getValue(), $response->getStatusCode());
    }

    public function testCanExecuteUsecaseWithoutRequestAndPresenter(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                throw new BadRequestContentException([
                    'message' => 'throw.error',
                    'details' => [
                        'field_1' => 'required',
                    ],
                ]);
            }
        };

        try {
            $usecase->execute();
        } catch (BadRequestContentException $exception) {
            $this->assertSame('throw.error', $exception->getMessage());
            $this->assertEquals([
                'details' => [
                    'field_1' => 'required',
                ],
            ], $exception->getErrors());

            $this->assertEquals([
                'status' => Status::ERROR->value,
                'error_code' => StatusCode::BAD_REQUEST->value,
                'message' => 'throw.error',
                'details' => [
                    'field_1' => 'required',
                ],
            ], $exception->format());
        }
    }

    public function testCanExecuteUsecaseAndReturnResponseWithContent(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                $this->presentResponse(Response::create(
                    statusCode: StatusCode::OK->getValue(),
                    data: [
                        'field_1' => $this->getField('field_1'),
                        'integer' => $this->getField('field_2.integer'),
                        'unknown' => $this->getField('field_2.unknown', 'default_value'),
                    ]
                ));
            }
        };

        $usecase
            ->withRequest($this->customRequest::createFromPayload([
                'field_1' => 'field_default_value',
                'field_2' => [
                    'boolean' => false,
                    'integer' => 666,
                    'float' => 666.666,
                ],
            ]))
            ->withPresenter($this->customPresenter)
            ->execute();

        $response = $this->customPresenter->getResponse();
        $this->assertNotNull($response);
        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->getMessage());
        $this->assertSame(StatusCode::OK->getValue(), $response->getStatusCode());

        $responseData = $response->getData();

        $this->assertArrayHasKey('field_1', $responseData);
        $this->assertSame('field_default_value', $responseData['field_1']);

        $this->assertArrayHasKey('integer', $responseData);
        $this->assertEquals(666, $responseData['integer']);

        $this->assertArrayHasKey('unknown', $responseData);
        $this->assertSame('default_value', $responseData['unknown']);
    }

    public function testCanExecuteUsecaseAndReturnFormattedSuccessResponse(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                $this->presentResponse(Response::create(
                    statusCode: StatusCode::OK->getValue(),
                    message: 'custom.message',
                    data: [
                        'field_1' => $this->getField('field_1'),
                        'float' => $this->getField('field_2.float'),
                    ]
                ));
            }
        };

        $payload = [
            'field_1' => 'field_value',
            'field_2' => [
                'boolean' => true,
                'integer' => 666,
                'float' => 666.666,
            ],
        ];

        $usecase
            ->withRequest($this->customRequest::createFromPayload($payload))
            ->withPresenter($this->customPresenter)
            ->execute();

        $responseAsArray = $this->customPresenter->getResponse()->output();

        $this->assertNotNull($responseAsArray);
        $this->assertEquals(Status::SUCCESS->value, $responseAsArray['status']);
        $this->assertSame(StatusCode::OK->getValue(), $responseAsArray['code']);
        $this->assertEquals('custom.message', $responseAsArray['message']);
        $this->assertEquals([
            'field_1' => 'field_value',
            'float' => 666.666,
        ], $responseAsArray['data']);
    }

    public function testCanExecuteUsecaseAndReturnFormattedErrorResponse(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                $this->presentResponse(Response::create(
                    success: false,
                    statusCode: StatusCode::NOT_FOUND->getValue(),
                    message: 'error.message',
                    data: [
                        'boolean' => $this->getField('field_2.boolean'),
                    ]
                ));
            }
        };

        $payload = [
            'field_2' => [
                'boolean' => true,
                'integer' => 666,
                'float' => 666.666,
            ],
        ];

        $usecase
            ->withRequest($this->customRequest::createFromPayload($payload))
            ->withPresenter($this->customPresenter)
            ->execute();

        $responseAsArray = $this->customPresenter->getResponse()->output();

        $this->assertNotNull($responseAsArray);
        $this->assertEquals(Status::ERROR->value, $responseAsArray['status']);
        $this->assertSame(StatusCode::NOT_FOUND->getValue(), $responseAsArray['code']);
        $this->assertEquals('error.message', $responseAsArray['message']);
        $this->assertEquals([
            'boolean' => true,
        ], $responseAsArray['details']);
    }
}
