# Core Library for Clean Architecture in PHP

## Introduction

This documentation guides you through the utilization of the core library for implementing clean architecture in PHP. We'll explore the creation of custom application requests and use cases, paying special attention to handling missing and unauthorized fields.

Practical examples are provided using code snippets to showcase the library's usage in building a modular and clean PHP application.

## Prerequisites

Ensure that you have the following:

- `PHP` installed on your machine (version `8.2.0 or higher`).
- `Composer` installed for dependency management.

## Installation

To install the core library, run the following command in your project directory:

```bash
composer require ug-php/clean-architecture-core
```

## Core Overview

### Application Request

Request serve as input objects, encapsulating data from your HTTP controller. In the core library, use the `\Urichy\Core\Request\Request` class as the foundation for creating custom application request objects.
Define the expected fields using the `requestPossibleFields` property.

```php
<?php

declare(strict_types=1);

use Urichy\Core\Request\Request;
use Urichy\Core\Request\RequestInterface;
use Assert\Assert;

final class PatientRecordRequest extends Request
{
    protected static array $requestPossibleFields = [
        'patient_name' => true, // required parameter
        'old' => true, // required parameter
        'medical_history' => [
            'allergies' => false, // optional parameter
            'current_medications' => true, // required nested parameter
            'past_surgeries' => [
                'surgery_name' => true, // required nested parameter
                'surgery_date' => true, // required nested parameter
            ],
        ],
    ];

    protected static function applyConstraintsOnRequestFields(array $requestData): void
    {
        Assert::that($requestData['patient_name'], '[patient_name] must not be an empty string.')->notEmpty()->string();
        Assert::that($requestData['old'], '[old] must be an integer.')->integer()->greaterThan(0);
        Assert::that($requestData['medical_history']['current_medications'], '[current_medications] must not be an empty string.')->notEmpty()->string();
        Assert::that($requestData['medical_history']['past_surgeries']['surgery_name'], '[surgery_name] must not be an empty string.')->notEmpty()->string();
        Assert::that($requestData['medical_history']['past_surgeries']['surgery_date'], '[surgery_date] must be a valid date.')->date();

        // Optional field constraint
        if (isset($requestData['medical_history']['allergies'])) {
            Assert::that($requestData['medical_history']['allergies'], '[allergies] must be a string.')->string();
        }
    }
}
```

Handling unauthorized fields:

```php
<?php

declare(strict_types=1);

try {
    PatientRecordRequest::createFromPayload([
        'patient_name' => 'Jane Doe',
        'old' => 45,
        'medical_history' => [
            'current_medications' => 'aspirin',
            'past_surgeries' => [
                'surgery_name' => 'Appendectomy',
                'surgery_date' => '2022-01-01',
            ],
            'extra_field' => 'unexpected',
        ],
    ]);
} catch (BadRequestContentException $exception) {
    // Handle unauthorized fields
    dd($exception->getErrors()); // ["medical_history.extra_field"]
}
```

Handling missing fields:

```php
<?php

declare(strict_types=1);

try {
    PatientRecordRequest::createFromPayload([
        'patient_name' => 'Jane Doe',
        'medical_history' => [
            'current_medications' => 'aspirin',
            'past_surgeries' => [
                'surgery_name' => 'Appendectomy',
            ],
        ],
    ]);
} catch (BadRequestContentException $exception) {
    // Handle missing fields
    dd($exception->getErrors()); // ["old" => "required", "medical_history.past_surgeries.surgery_date" => "required"]
}
```

When request successfully created.

```php
<?php

declare(strict_types=1);

$request = PatientRecordRequest::createFromPayload([
    'patient_name' => 'Jane Doe',
    'old' => 45,
    'medical_history' => [
        'current_medications' => 'aspirin',
        'past_surgeries' => [
            'surgery_name' => 'Appendectomy',
            'surgery_date' => '2022-01-01',
        ],
    ],
]);

dd($request->getRequestId()); // 6d326314-f527-483c-80df-7c157acdb95b
dd([
    'patient_name' => $request->get('patient_name'), 
    'current_medications' => $request->get('medical_history.current_medications'),
    'unknown' => $request->get('unknown', 'default_value'),
]); // ['patient_name' => 'Jane Doe', 'current_medications' => 'aspirin', 'unknown' => 'default_value']

dd($request->toArray());
/*
[
    'patient_name' => 'Jane Doe',
    'old' => 45,
    'medical_history' => [
        'current_medications' => 'aspirin',
        'past_surgeries' => [
            'surgery_name' => 'Appendectomy',
            'surgery_date' => '2022-01-01',
        ],
    ],
]*/

```

### Presenter

Presenter handle the output logic of your use case. Extend `\Urichy\Core\Presenter\Presenter` and implement `\Urichy\Core\Presenter\PresenterInterface`.

```php
<?php

declare(strict_types=1);

use Urichy\Core\Presenter\Presenter;
use Urichy\Core\Presenter\PresenterInterface;
use Urichy\Core\Response\ResponseInterface;

final class ArrayResponsePresenter extends Presenter
{
    public function getResponse(): array
    {
        return $this->response->output();
    }
}
```

### Response

Response encapsulate the data returned by use cases. They include status information, messages, and any relevant data. Use `\Urichy\Core\Response\Response` to create use case responses.

```php
<?php

declare(strict_types=1);

use Urichy\Core\Response\Response;

// success response
$response = Response::create(
    success: true,
    statusCode: StatusCode::OK->value,
    message: 'success.response',
    data: [
        'user_id' => '6d326314-f527-483c-80df-7c157acdb95b',
    ]
)

// or failed response
$response = Response::create(
    success: false,
    statusCode: StatusCode::NOT_FOUND->value,
    message: 'failed.response',
    data: [
        'field' => 'value',
    ]
)

dd($response->isSuccess()); // true or false
dd($response->getStatusCode()); // 200 or 404
dd($response->getMessage()); // 'success.response' or 'failed.response'
dd($response->getData()); // ['field' => 'value'] or ['user_id' => '6d326314-f527-483c-80df-7c157acdb95b']
dd($response->get('field')); // 'value'
dd($response->get('unknown_field')); // null
```

### Use Case

Use cases encapsulate business logic and orchestrate the flow of data between requests, entities, and presenters. Extend the `\Urichy\Core\Usecase\Usecase` class and implement `\Urichy\Core\Usecase\UsecaseInterface` with the `execute` method.

`@see example below`

### Exception

When an exception is thrown during processing, you can use some method to handle the exception data.

How to create an exception ?

1. Create an exception class that extends `\Urichy\Core\Exception\Exception`

```php
<?php

declare(strict_types=1);

use Urichy\Core\Exception\Exception;

final class BadRequestContentException extends Exception
{
}

final class UserNotFoundException extends Exception
{
}
```

2. Throw an exception when something has gone wrong and handling it.

```php
<?php

declare(strict_types=1);

use Urichy\Core\Exception\Exception;
use Urichy\Core\Exception\BadRequestContentException;
use Urichy\Core\Exception\UserNotFoundException;

try {
    //...
    throw new BadRequestContentException([
        'message' => 'bad.request.content',
        'details' => [
            'email' => [
                '[email] field is required.',
                '[email] must be a valid email.',
            ]
        ] // array with error contexts
    ]);
    // or
    throw new UserNotFoundException([
        'message' => 'user.not.found',
        'details'  => [
            'error' => 'User with [ulrich] username not found.'
        ] // array with error contexts
    ]);
} catch(ExceptionInterface $exception) {
    // for exception, some method are available
    dd($exception->getErrors()); // print details
    [
        'details' => [
            'email' => [
                '[email] field is required.',
                '[email] must be a valid email.',
            ]
        ],
    ]
    // or
    [
        'details' => [
            'error' => 'User with [ulrich] username not found.',
        ],
    ]

    dd($exception->getDetails()); // print error details
    [
        'email' => [
            '[email] field is required.',
            '[email] must be a valid email.',
        ]
    ]

    // or 

    [
        'error' => 'User with [ulrich] username not found.',
    ],

    dd($exception->getMessage()) // 'error.message'
    dd($exception->getDetailsMessage()) // 'User with [ulrich] username not found.', only if 'error' key is defined in details.

    dd($exception->getErrorsForLog()) // print error with more context
    [
        'message' => $this->getMessage(),
        'code' => $this->getCode(),
        'errors' => $this->errors,
        'file' => $this->getFile(),
        'line' => $this->getLine(),
        'previous' => $this->getPrevious(),
        'trace_as_array' => $this->getTrace(),
        'trace_as_string' => $this->getTraceAsString(),
    ]

    dd($exception->format());
    [
        'status' => 'success' or 'error',
        'error_code' => 400,
        'message' => 'throw.error',
        'details' => [
            'email' => [
                '[email] field is required.',
                '[email] must be a valid email.',
            ],
            'lastname' => [
                '[lastname] field is required.',
            ]
        ],
    ]
}
```

## Example Usage

### From Scratch (PHP Without a Framework)

#### Project Structure

```
├── src
│   ├── Controller
│   │   └── BookController.php
│   ├── Request
│   │   └── BookRecordRequest.php
│   ├── Presenter
│   │   └── JsonResponsePresenter.php
│   ├── UseCase
│   │   └── RegisterBookUsecase.php
│   └── Response
│       └── Response.php
├── public
│   └── index.php
└── composer.json
```

#### Code Examples

##### `public/index.php`

```php
<?php

declare(strict_types=1);

require '../vendor/autoload.php';

use App\Controller\BookController;
use Symfony\Component\HttpFoundation\Request;

$request = Request::createFromGlobals();
$controller = new BookController();
$response = $controller->registerBook($request);
$response->send();
```

##### `src/Controller/BookController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\BookRecordRequest;
use App\Presenter\JsonResponsePresenter;
use App\UseCase\RegisterBookUsecase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

final class BookController
{
    public function registerBook(SymfonyRequest $request): JsonResponse
    {
        $bookRequest = BookRecordRequest::createFromPayload([
            'title' => $request->get('title'),
            'publication' => [
                'date' => $request->get('published_date'),
                'publisher' => $request->get('publisher'),
            ],
            'isbn' => $request->get('isbn'),
        ]);

        // you can also use $request->toArray() (in createFromPayload method) to get request payload if POST request

        $presenter = new JsonResponsePresenter();
        $useCase = new RegisterBookUsecase();
        $useCase
            ->withRequest($bookRequest)
            ->withPresenter($presenter)
            ->execute();

        return $presenter->getResponse();
    }
}
```

##### `src/Request/BookRecordRequest.php`

###### Using `beberlei/assert` validation library

```php
<?php

declare(strict_types=1);

namespace App\Request;

use Urichy\Core\Request\Request;
use Urichy\Core\Request\RequestInterface;
use Assert\Assert;

// interface is optional. You can directly use the implementation
interface BookRecordRequestInterface extends RequestInterface {}

final class BookRecordRequest extends Request implements BookRecordRequestInterface
{
    protected static array $requestPossibleFields = [
        'title' => true, // required parameters
        'publication' => [
            'date' => true,
            'publisher' => false, // optional parameters
        ],
        'isbn' => true,
    ];

    /**
     * @param array<string, mixed> $requestData
     * @return void
     */
    protected static function applyConstraintsOnRequestFields(array $requestData): void
    {
        Assert::that($requestData['title'], '[title] must not be an empty string.')->notEmpty()->string();
        Assert::that($requestData['publication']['date'], '[date] must be a valid date.')->date();
        Assert::that($requestData['isbn'], '[isbn] must not be an empty string.')->notEmpty()->string();
        if (isset($requestData['publication']['publisher'])) {
            Assert::that($requestData['publication']['publisher'], '[publisher] must be a string.')->string();
        }
    }
}
```

###### Using `Symfony Validator` library

```php
<?php

declare(strict_types=1);

namespace App\Request;

use Urichy\Core\Request\Request;
use Urichy\Core\Request\RequestInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as SymfonyAssert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

// interface is optional. You can directly use the implementation
interface BookRecordRequestInterface extends RequestInterface {}

final class BookRecordRequest extends Request implements BookRecordRequestInterface
{
    protected static array $requestPossibleFields = [
        'title' => true,
        'publication' => [
            'date' => true,
            'publisher' => false,
        ],
        'isbn' => true,
    ];

    /**
     * @param array<string, mixed> $requestData
     * @return void
     */
    protected static function applyConstraintsOnRequestFields(array $requestData): void
    {
        $validator = Validation::createValidator();
        $constraints = [
            'title' => [
                new SymfonyAssert\NotBlank(message: '[title] cannot be blank'),
                new SymfonyAssert\Type(type: 'string', message: '[title] must be a string'),
            ],
            'publication' => new SymfonyAssert\Collection([
                'date' => [
                    new SymfonyAssert\NotBlank(message: '[date] cannot be blank'),
                    new SymfonyAssert\Date(message: '[date] must be a valid date'),
                ]
            ]),
            'isbn' => [
                new SymfonyAssert\NotBlank(message: '[isbn] cannot be blank'),
                new SymfonyAssert\Type(type: 'string', message: '[isbn] must be a string'),
            ],
        ];

        if (isset($requestData['publication']['publisher'])) {
            $constraints['publication']['publisher'] = [
                new SymfonyAssert\Type(type: 'string', message: '[publisher] must be a string'),
            ];
        }

        $violations = $validator->validate($requestData, new SymfonyAssert\Collection($constraints));

        self::throwViolationsWhenErrors($violations);
    }

    private static function throwViolationsWhenErrors(ConstraintViolationListInterface $violations): void
    {
        $errors = [];
        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $errors[$propertyPath][] = $violation->getMessage();
        }

        if (!empty($errors)) {
            $errors['message'] = 'invalid.request.field';
            throw new BadRequestContentException($errors);
        }
    }
}
```

##### `src/Presenter/JsonResponsePresenter.php`

```php
<?php

declare(strict_types=1);

namespace App\Presenter;

use Urichy\Core\Presenter\Presenter;
use Urichy\Core\Presenter\PresenterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JsonResponsePresenter extends Presenter implements PresenterInterface
{
    public function getResponse(): JsonResponse
    {
        $responseData = $this->response->output();
        return new JsonResponse($responseData, $responseData['code']);
    }
}
```

##### `src/Presenter/HtmlResponsePresenter.php`

```php
<?php

declare(strict_types=1);

namespace App\Presenter;

use Urichy\Core\Presenter\Presenter;
use Urichy\Core\Presenter\PresenterInterface;
use Symfony\Component\HttpFoundation\Response;

final class HtmlResponsePresenter extends Presenter implements PresenterInterface
{
    public function getResponse(): Response
    {
        $responseData = $this->response->output();
        $htmlContent = "<html><body><h1>{$responseData['message']}</h1><p>" . json_encode($responseData['data']) . "</p></body></html>";
        return new Response($htmlContent, $responseData['code']);
    }
}
```

##### `src/UseCase/RegisterBookUsecase.php`

```php
<?php

declare(strict_types=1);

namespace App\UseCase;

use Urichy\Core\Usecase\Usecase;
use Urichy\Core\Usecase\UsecaseInterface;
use Urichy\Core\Response\Response;
use Urichy\Core\Response\StatusCode;

interface RegisterBookUsecaseInterface extends UsecaseInterface {}

final class RegisterBookUsecase extends Usecase implements RegisterBookUsecaseInterface
{
    public function __construct(
        // inject your dependencies here (always use dependencie interface, not implementation)
        private BookRepositoryInterface $bookRepository
    ) {}

    public function execute(): void
    {
        $requestData = $this->getRequestData();
        $requestId = $this->getRequestId();

        $book = [
            'title' => $this->getField('title'),
            'author' => $this->getField('publication.publisher'),
            'publication_date' => $this->getField('publication.date'),
            'isbn' => $this->getField('isbn'),
        ];

        // process your business logic here
        try {
            $this->bookRepository->save(Book::from($book))
        } catch (PersistenceException $e) {
            // handle persistence exception here or log it or send failed response.
        }

        $this->presentResponse(Response::create(
            success: true,
            statusCode: StatusCode::OK->value,
            message: 'book.registered.successfully.',
            data: $book
        ));
    }
}
```

##### `src/Response/Response.php`

```php
<?php

declare(strict_types=1);

namespace App\Response;

use Urichy\Core\Response\Response as LibResponse;
use Urichy\Core\Response\StatusCode;

abstract class Response extends LibResponse
{
    public static function createSuccessResponse(array $data, StatusCode $statusCode, ?string $message = null): self
    {
        return new self(true, $statusCode->value, $message, $data);
    }

    public static function createFailedResponse(array $errors = [], StatusCode $statusCode, ?string $message = null): self
    {
        return new self(false, $statusCode->value, $message, $errors);
    }
}
```

### Example with Symfony

#### Project Structure

```
├── src
│   ├── Controller
│   │   └── BookController.php
│   ├── Request
│   │   └── BookRecordRequest.php
│   ├── Presenter
│   │   └── JsonResponsePresenter.php
|   |   └── HtmlResponsePresenter.php
│   ├── UseCase
│   │   └── RegisterBookUsecase.php
│   └── Response
│       └── Response.php
├── public
│   └── index.php
├── config
│   └── services.yaml
└── composer.json
```

#### Code Examples

##### `src/Controller/BookController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Request\BookRecordRequest;
use App\Presenter\JsonResponsePresenter;
use App\Presenter\HtmlResponsePresenter;
use App\UseCase\RegisterBookUsecase;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/register-book', name: 'register_book', methods: 'POST')]
final class BookController extends AbstractController
{
    public function __construct(
        private readonly RegisterBookUsecase $registerBookUsecase
    ) {}

    public function __invoke(SymfonyRequest $request): JsonResponse
    {
        try {
            $bookRequest = BookRecordRequest::createFromPayload([
                'title' => $request->get('title'),
                'author' => $request->get('author'),
                'publication' => [
                    'published_date' => $request->get('published_date'),
                    'publisher' => $request->get('publisher'),
                ],
                'isbn' => $request->get('isbn'),
            ]);

            $presenter = $this-getPresenterAccordingToRequestContentType($request->getContentType());
            $this->registerBookUsecase
                ->withRequest($bookRequest)
                ->withPresenter($presenter)
                ->execute();

            $response = $presenter->getResponse()->output();
        } catch (Exception $exception) {
            return $this->json($exception->format(), $exception->getCode());
        }

        return $this->json($response, $response['code']);
    }

    // you can instanciate presenter according to the request context
    private function getPresenterAccordingToRequestContentType(string $contentType): PresenterInterface
    {
        switch ($contentType) {
            case 'text/html':
                return new HtmlResponsePresenter();
            default:
                break;
        }
        return new JsonResponsePresenter();
    }
}
```

## Example with Laravel

#### Project Structure

```
├── app
│   ├── Http
│   │   └── Controllers
│   │       └── BookController.php
│   ├── Requests
│   │   └── BookRecordRequest.php
│   ├── Presenters
│   │   └── JsonResponsePresenter.php
│   ├── UseCases
│   │   └── RegisterBookUsecase.php
│   └── Responses
│       └── Response.php
├── public
│   └── index.php
└── composer.json
```

#### Code Examples

##### `app/Http/Controllers/BookController.php`

With request and presenter

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Requests\BookRecordRequest;
use App\Presenters\JsonResponsePresenter;
use App\Presenters\HtmlResponsePresenter;
use App\UseCases\RegisterBookUsecase;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Http\JsonResponse;

final class BookController extends Controller
{
    public function __construct(
        private readonly RegisterBookUsecase $registerBookUsecase
    ) {}

    public function __invoke(LaravelRequest $request): JsonResponse
    {
        try {
            $bookRequest = BookRecordRequest::createFromPayload([
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'publication' => [
                    'published_date' => $request->input('published_date'),
                    'publisher' => $request->input('publisher'),
                ],
                'isbn' => $request->input('isbn'),
            ]);

            $jsonPresenter = new JsonResponsePresenter();
            $this
                ->registerBookUsecase
                ->withRequest($bookRequest)
                ->withPresenter($jsonPresenter)
                ->execute();

            $response = $jsonPresenter->getResponse()->output();
        } catch (Exception $exception) {
            return response()->json($exception->format(), $exception->getCode());
        }

        return response()->json($response, $response['code']);
    }
}
```

Without presenter, but with request.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Requests\BookRecordRequest;
use App\UseCases\RegisterBookUsecase;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Http\JsonResponse;

final class BookController extends Controller
{
    public function __construct(
        private readonly RegisterBookUsecase $registerBookUsecase
    ) {}

    public function __invoke(LaravelRequest $request): JsonResponse
    {
        try {
            $bookRequest = BookRecordRequest::createFromPayload([
                'title' => $request->input('title'),
                'author' => $request->input('author'),
                'publication' => [
                    'published_date' => $request->input('published_date'),
                    'publisher' => $request->input('publisher'),
                ],
                'isbn' => $request->input('isbn'),
            ]);

            $this
                ->registerBookUsecase
                ->withRequest($bookRequest)
                ->execute();

        } catch (Exception $exception) {
            return response()->json($exception->format(), $exception->getCode());
        }

        return response()->json([]);
    }
}
```

Without request and presenter

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Requests\BookRecordRequest;
use App\UseCases\RegisterBookUsecase;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Http\JsonResponse;

final class BookController extends Controller
{
    public function __construct(
        private readonly RegisterBookUsecase $registerBookUsecase
    ) {}
    
    public function __invoke(): JsonResponse
    {
        try {
            $this
                ->registerBookUsecase
                ->execute();

        } catch (Exception $exception) {
            return response()->json($exception->format(), $exception->getCode());
        }

        return response()->json([]);
    }
}
```

## Unit Tests

### Command to Run Unit Tests

```bash
$ make tests
```

## License

### Information on Copyright and MIT License

- Written and copyrighted ©2023-present by Ulrich Geraud AHOGLA. <iamcleancoder@gmail.com>
- Clean architecture core is open-sourced software licensed under the [MIT license](http://www.opensource.org/licenses/mit-license.php)