<?php

use Davoodf1995\Desk365\DTO\{
    ApiConfigDto,
    ApiResponseDto,
    TicketCreateDto,
    TicketUpdateDto,
    CommentDto
};

it('can create ApiConfigDto from array', function () {
    $data = [
        'base_url' => 'https://api.desk365.com',
        'api_key' => 'test_key',
        'timeout' => 60,
        'version' => 'v3'
    ];

    $config = ApiConfigDto::fromArray($data);

    expect($config->baseUrl)->toBe('https://api.desk365.com')
        ->and($config->apiKey)->toBe('test_key')
        ->and($config->timeout)->toBe(60)
        ->and($config->version)->toBe('v3');
});

it('can get auth headers from ApiConfigDto', function () {
    $config = new ApiConfigDto(
        baseUrl: 'https://api.desk365.com',
        apiKey: 'test_key',
        apiSecret: 'test_secret'
    );

    $headers = $config->getAuthHeaders();

    expect($headers)->toHaveKey('Authorization')
        ->and($headers['Authorization'])->toBe('Bearer test_key')
        ->and($headers)->toHaveKey('X-API-Secret')
        ->and($headers['X-API-Secret'])->toBe('test_secret');
});

it('can create success ApiResponseDto', function () {
    $response = ApiResponseDto::success(
        data: ['id' => '123'],
        message: 'Success',
        statusCode: 200
    );

    expect($response->isSuccess())->toBeTrue()
        ->and($response->getData())->toBe(['id' => '123'])
        ->and($response->getMessage())->toBe('Success')
        ->and($response->getStatusCode())->toBe(200);
});

it('can create error ApiResponseDto', function () {
    $response = ApiResponseDto::error(
        message: 'Error occurred',
        errors: ['field' => 'Error message'],
        statusCode: 400
    );

    expect($response->isError())->toBeTrue()
        ->and($response->getMessage())->toBe('Error occurred')
        ->and($response->getErrors())->toBe(['field' => 'Error message'])
        ->and($response->getStatusCode())->toBe(400);
});

it('can convert TicketCreateDto to array', function () {
    $dto = new TicketCreateDto(
        email: 'test@example.com',
        subject: 'Test',
        description: 'Description',
        assignedTo: 'agent_1',
        group: 'support',
        category: 'technical',
        status: 'open',
        priority: 2
    );

    $array = $dto->toArray();

    expect($array)->toHaveKey('email')
        ->and($array)->toHaveKey('subject')
        ->and($array)->toHaveKey('status')
        ->and($array['status'])->toBe('open');
});

it('can convert TicketUpdateDto to array', function () {
    $dto = new TicketUpdateDto(
        status: 'closed',
        priority: 3
    );

    $array = $dto->toArray();

    expect($array)->toHaveKey('status')
        ->and($array['status'])->toBe('closed')
        ->and($array)->toHaveKey('priority')
        ->and($array['priority'])->toBe(3);
});

it('can convert CommentDto to array', function () {
    $dto = new CommentDto(
        content: 'Test comment',
        authorId: 'agent_1',
        authorType: 'agent',
        isPublic: true
    );

    $array = $dto->toArray();

    expect($array)->toHaveKey('content')
        ->and($array)->toHaveKey('author_id')
        ->and($array['content'])->toBe('Test comment');
});

