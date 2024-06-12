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
use Urichy\Core\Presenter\Presenter as BasePresenter;
use Urichy\Core\Presenter\PresenterInterface;
use Urichy\Core\Request\Request as BaseRequest;
use Urichy\Core\Request\RequestInterface;
use Urichy\Core\Response\Response;
use Urichy\Core\Response\StatusCode;
use Urichy\Core\Usecase\Usecase as BaseUsecase;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class UsecaseTest extends TestCase
{
    private RequestInterface $customRequest;
    private PresenterInterface $customPresenter;

    protected function setUp(): void
    {
        $this->customRequest = new class () extends BaseRequest {
            protected static array $requestPossibleFields = [
                'field_1' => null,
                'field_2' => null,
            ];
        };

        $this->customPresenter = new class () extends BasePresenter {
        };
    }

    public function testCanExecuteUsecaseAndReturnEmptyResponse(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                $this->presenter->present(Response::create());
            }
        };

        $usecase
            ->setRequest($this->customRequest::createFromPayload([
                'field_1' => 'field_value',
                'field_2' => [
                    'boolean' => true,
                    'integer' => 666,
                    'float' => 666.666,
                ],
            ]))
            ->setPresenter($this->customPresenter)
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
                $this->presenter->present(Response::create(
                    statusCode: StatusCode::OK->getValue(),
                    data: [
                        'request' => $this->request,
                        'request_data' => $this->getRequestData(),
                    ]
                ));
            }
        };

        $usecase
            ->setPresenter($this->customPresenter)
            ->execute();

        $response = $this->customPresenter->getResponse();
        $this->assertNull($response->get('request'));
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
                $this->presenter->present(Response::create(
                    statusCode: StatusCode::OK->getValue(),
                    data: (array)$this->getRequestData()
                ));
            }
        };

        $usecase
            ->setRequest($this->customRequest::createFromPayload([
                'field_1' => 'field_default_value',
                'field_2' => [
                    'boolean' => false,
                    'integer' => 666,
                    'float' => 666.666,
                ],
            ]))
            ->setPresenter($this->customPresenter)
            ->execute();

        $response = $this->customPresenter->getResponse();
        $this->assertNotNull($response);
        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->getMessage());
        $this->assertSame(StatusCode::OK->getValue(), $response->getStatusCode());

        $responseData = $response->getData();

        $this->assertArrayHasKey('field_2', $responseData);
        $this->assertSame('field_default_value', $responseData['field_1']);

        $this->assertArrayHasKey('boolean', $responseData['field_2']);
        $this->assertFalse($responseData['field_2']['boolean']);

        $this->assertArrayHasKey('integer', $responseData['field_2']);
        $this->assertSame(666, $responseData['field_2']['integer']);

        $this->assertArrayHasKey('float', $responseData['field_2']);
        $this->assertSame(666.666, $responseData['field_2']['float']);
    }

    public function testCanExecuteUsecaseAndReturnFormattedSuccessResponse(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                $this->presenter->present(Response::create(
                    statusCode: StatusCode::OK->getValue(),
                    message: 'custom.message',
                    data: (array)$this->getRequestData()
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
            ->setRequest($this->customRequest::createFromPayload($payload))
            ->setPresenter($this->customPresenter)
            ->execute();

        $response = $this->customPresenter->getFormattedResponse();

        $this->assertNotNull($response);
        $this->assertEquals(Status::SUCCESS->value, $response['status']);
        $this->assertSame(StatusCode::OK->getValue(), $response['code']);
        $this->assertEquals('custom.message', $response['message']);
        $this->assertEquals($payload, $response['data']);
    }

    public function testCanExecuteUsecaseAndReturnFormattedErrorResponse(): void
    {
        $usecase = new class () extends BaseUsecase {
            public function execute(): void
            {
                $this->presenter->present(Response::create(
                    success: false,
                    statusCode: StatusCode::NOT_FOUND->getValue(),
                    message: 'error.message',
                    data: (array)$this->getRequestData()
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
            ->setRequest($this->customRequest::createFromPayload($payload))
            ->setPresenter($this->customPresenter)
            ->execute();

        $response = $this->customPresenter->getFormattedResponse();

        $this->assertNotNull($response);
        $this->assertEquals(Status::ERROR->value, $response['status']);
        $this->assertSame(StatusCode::NOT_FOUND->getValue(), $response['code']);
        $this->assertEquals('error.message', $response['message']);
        $this->assertEquals($payload, $response['details']);
    }
}
