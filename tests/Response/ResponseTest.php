<?php

/*
 * This file is part of the Urichy Core package.
 *
 * (c) Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
 */

declare(strict_types=1);

namespace Urichy\Tests\Core\Response;

use PHPUnit\Framework\TestCase;
use Urichy\Core\Enum\Status;
use Urichy\Core\Response\Response;
use Urichy\Core\Response\StatusCode;

/**
 * @author Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com
 */
final class ResponseTest extends TestCase
{
    public function testCanCreateResponseWithoutContent(): void
    {
        $response = Response::create();

        $this->assertTrue($response->isSuccess());
        $this->assertNull($response->getMessage());
        $this->assertEquals(StatusCode::NO_CONTENT->getValue(), $response->getStatusCode());
        $this->assertCount(0, $response->getData());
    }

    public function testCanCreateNewSuccessResponseWithOkStatusCode(): void
    {
        $responseData = [
            'field' => 1,
        ];
        $response = Response::create(
            statusCode: StatusCode::OK->getValue(),
            message: 'Successfully get resource.',
            data: $responseData
        );

        $this->assertTrue($response->isSuccess());
        $this->assertEquals(StatusCode::OK->getValue(), $response->getStatusCode());
        $this->assertEquals('Successfully get resource.', $response->getMessage());
        $this->assertEquals($responseData, $response->getData());
    }

    public function testCanCreateNewSuccessResponseWithCreatedStatusCode(): void
    {
        $responseData = [
            'field_1' => 1,
            'field_2' => 'value',
        ];
        $response = Response::create(
            statusCode: StatusCode::CREATED->getValue(),
            message: 'Successfully create resource.',
            data: $responseData
        );

        $this->assertTrue($response->isSuccess());
        $this->assertEquals(StatusCode::CREATED->getValue(), $response->getStatusCode());
        $this->assertEquals('Successfully create resource.', $response->getMessage());
        $this->assertEquals($responseData, $response->getData());
    }

    public function testCanCreateNewFailedResponseWithBadStatusCode(): void
    {
        $response = Response::create(
            success: false,
            statusCode: StatusCode::BAD_REQUEST->getValue(),
            data: []
        );

        $this->assertFalse($response->isSuccess());
        $this->assertNull($response->getMessage());
        $this->assertEquals(StatusCode::BAD_REQUEST->getValue(), $response->getStatusCode());
    }

    public function testCanCreateNewResponseAndGetExistingDataByKey(): void
    {
        $response = Response::create(
            success: false,
            statusCode: StatusCode::BAD_REQUEST->getValue(),
            data: [
                'key_1' => 1234,
            ]
        );

        $this->assertNotNull($response->get('key_1'));
    }

    public function testCanCreateNewResponseAndGetNotExistingDataByKey(): void
    {
        $response = Response::create(
            success: false,
            statusCode: StatusCode::BAD_REQUEST->getValue(),
            data: [
                'key_1' => 1234,
            ]
        );

        $this->assertNull($response->get('key_2'));
    }

    public function testCanCreateNewResponseAndGetChainingExistingDataByKey(): void
    {
        $response = Response::create(
            success: false,
            statusCode: StatusCode::BAD_REQUEST->getValue(),
            data: [
                'key_1' => [
                    'key_2' => [
                        'key_3' => 1234,
                    ],
                ],
            ]
        );

        $this->assertNull($response->get('key_1.key_2.key_3.key_4'));
        $this->assertEquals(1234, $response->get('key_1.key_2.key_3'));
    }

    public function testCanCreateNewResponseAndGetSuccessOutput(): void
    {
        $responseData = [
            'key_1' => [
                'key_2' => [
                    'key_3' => 1234,
                ],
            ],
        ];

        $response = Response::create(
            statusCode: StatusCode::BAD_REQUEST->getValue(),
            message: 'custom.message',
            data: $responseData
        );

        $this->assertEquals([
            'status' => Status::SUCCESS->value,
            'code' => StatusCode::BAD_REQUEST->getValue(),
            'message' => 'custom.message',
            'data' => $responseData,
        ], $response->output());
    }

    public function testCanCreateNewResponseAndGetErrorOutput(): void
    {
        $responseData = [
            'key_2' => [
                'key_3' => 1234,
            ],
        ];

        $response = Response::create(
            success: false,
            statusCode: StatusCode::BAD_REQUEST->getValue(),
            message: 'error.message',
            data: $responseData
        );

        $this->assertEquals([
            'status' => Status::ERROR->value,
            'code' => StatusCode::BAD_REQUEST->getValue(),
            'message' => 'error.message',
            'details' => $responseData,
        ], $response->output());
    }
}
