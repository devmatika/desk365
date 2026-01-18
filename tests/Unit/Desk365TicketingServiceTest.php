<?php

use Devmatika\Desk365\Services\Desk365TicketingService;
use Devmatika\Desk365\DTO\{
    ApiConfigDto,
    TicketCreateDto,
    TicketUpdateDto,
    CommentDto,
    ApiResponseDto
};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    $this->config = new ApiConfigDto(
        baseUrl: 'https://api.desk365.com',
        apiKey: 'test_api_key',
        timeout: 30,
        version: 'v3'
    );
    
    $this->service = new Desk365TicketingService($this->config);
});

it('can create a ticket without file', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/create' => Http::response([
            'success' => true,
            'data' => ['id' => '123', 'ticket_number' => 'TKT-001'],
            'message' => 'Ticket created successfully'
        ], 200)
    ]);

    $ticketData = new TicketCreateDto(
        email: 'test@example.com',
        subject: 'Test Ticket',
        description: 'This is a test ticket',
        assignedTo: 'agent_1',
        group: 'support',
        category: 'technical'
    );

    $response = $this->service->createTicket($ticketData);

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue()
        ->and($response->getData())->toHaveKey('id');
});

it('can create a ticket with file attachment', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/create_with_attachment*' => Http::response([
            'success' => true,
            'data' => ['id' => '124', 'ticket_number' => 'TKT-002'],
            'message' => 'Ticket created with attachment'
        ], 200)
    ]);

    $file = fopen('php://temp', 'r+');
    fwrite($file, 'test file content');
    rewind($file);

    $ticketData = new TicketCreateDto(
        email: 'test@example.com',
        subject: 'Test Ticket with File',
        description: 'This is a test ticket with file',
        assignedTo: 'agent_1',
        group: 'support',
        category: 'technical',
        file: $file
    );

    $response = $this->service->createTicket($ticketData);

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue();
    
    fclose($file);
});

it('can update a ticket', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/update*' => Http::response([
            'success' => true,
            'data' => ['id' => '123', 'status' => 'closed'],
            'message' => 'Ticket updated successfully'
        ], 200)
    ]);

    $updateData = new TicketUpdateDto(
        status: 'closed',
        priority: 2
    );

    $response = $this->service->updateTicket('TKT-001', $updateData);

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue();
});

it('can close a ticket', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/update*' => Http::response([
            'success' => true,
            'data' => ['id' => '123', 'status' => 'closed'],
            'message' => 'Ticket closed'
        ], 200)
    ]);

    $response = $this->service->closeTicket('TKT-001');

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue();
});

it('can reopen a ticket', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/update*' => Http::response([
            'success' => true,
            'data' => ['id' => '123', 'status' => 'open'],
            'message' => 'Ticket reopened'
        ], 200)
    ]);

    $response = $this->service->reopenTicket('TKT-001');

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue();
});

it('can add a comment to a ticket', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/add_reply' => Http::response([
            'success' => true,
            'data' => ['comment_id' => '456'],
            'message' => 'Comment added successfully'
        ], 200)
    ]);

    $comment = new CommentDto(
        content: 'This is a test comment',
        authorId: 'agent_1',
        authorType: 'agent'
    );

    $response = $this->service->addComment('TKT-001', $comment);

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue();
});

it('can update ticket status', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/update*' => Http::response([
            'success' => true,
            'data' => ['id' => '123', 'status' => 'in_progress'],
            'message' => 'Status updated'
        ], 200)
    ]);

    $response = $this->service->updateTicketStatus('TKT-001', 'in_progress');

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue();
});

it('can update ticket priority', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/update*' => Http::response([
            'success' => true,
            'data' => ['id' => '123', 'priority' => 3],
            'message' => 'Priority updated'
        ], 200)
    ]);

    $response = $this->service->updateTicketPriority('TKT-001', '3');

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isSuccess())->toBeTrue();
});

it('handles API errors correctly', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/create' => Http::response([
            'success' => false,
            'message' => 'Invalid API key',
            'errors' => ['api_key' => 'Invalid or expired']
        ], 401)
    ]);

    $ticketData = new TicketCreateDto(
        email: 'test@example.com',
        subject: 'Test Ticket',
        description: 'This is a test ticket',
        assignedTo: 'agent_1',
        group: 'support',
        category: 'technical'
    );

    $response = $this->service->createTicket($ticketData);

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isError())->toBeTrue()
        ->and($response->getStatusCode())->toBe(401);
});

it('handles network exceptions', function () {
    Http::fake([
        'api.desk365.com/api/v3/tickets/create' => Http::response([], 500)
    ]);

    Log::shouldReceive('error')
        ->once()
        ->with('Desk365 API Error - Create Ticket', \Mockery::type('array'));

    $ticketData = new TicketCreateDto(
        email: 'test@example.com',
        subject: 'Test Ticket',
        description: 'This is a test ticket',
        assignedTo: 'agent_1',
        group: 'support',
        category: 'technical'
    );

    $response = $this->service->createTicket($ticketData);

    expect($response)->toBeInstanceOf(ApiResponseDto::class)
        ->and($response->isError())->toBeTrue();
});



