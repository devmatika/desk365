# Desk365 API Integration for Laravel

Laravel Package for integration with Desk365 Ticketing API.

## Installation

You can install the package via composer:

```bash
composer require davoodf1995/desk365
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="desk365-config"
```

This is the contents of the published config file:

```php
return [
    'base_url' => env('DESK365_BASE_URL', 'https://api.desk365.com'),
    'api_key' => env('DESK365_API_KEY', ''),
    'api_secret' => env('DESK365_API_SECRET', null),
    'timeout' => env('DESK365_TIMEOUT', 30),
    'retry_attempts' => env('DESK365_RETRY_ATTEMPTS', 3),
    'version' => env('DESK365_API_VERSION', 'v3'),
];
```

## Configuration

Add the following to your `.env` file:

```env
DESK365_BASE_URL=https://api.desk365.com
DESK365_API_KEY=your_api_key_here
DESK365_API_SECRET=your_api_secret_here
DESK365_TIMEOUT=30
DESK365_RETRY_ATTEMPTS=3
DESK365_API_VERSION=v3
```

## Usage

### Using the Facade

```php
use Davoodf1995\Desk365\Facades\Desk365;
use Davoodf1995\Desk365\DTO\TicketCreateDto;
use Davoodf1995\Desk365\DTO\CommentDto;

// Create a ticket
$ticketData = new TicketCreateDto(
    email: 'customer@example.com',
    subject: 'Support Request',
    description: 'I need help with...',
    assignedTo: 'agent_id',
    group: 'support_group',
    category: 'technical'
);

$response = Desk365::createTicket($ticketData);

// Update ticket status
$response = Desk365::updateTicketStatus('ticket_id', 'closed');

// Add a comment
$comment = new CommentDto(
    content: 'This is a comment',
    authorId: 'agent_id',
    authorType: 'agent'
);

$response = Desk365::addComment('ticket_id', $comment);
```

### Using the Service Directly

```php
use Davoodf1995\Desk365\Services\Desk365TicketingService;
use Davoodf1995\Desk365\DTO\ApiConfigDto;
use Davoodf1995\Desk365\DTO\TicketCreateDto;

$config = new ApiConfigDto(
    baseUrl: 'https://api.desk365.com',
    apiKey: 'your_api_key',
    timeout: 30,
    version: 'v3'
);

$service = new Desk365TicketingService($config);
$response = $service->createTicket($ticketData);
```

## Available Methods

### Ticket Operations
- `createTicket(TicketCreateDto $ticketData): ApiResponseDto`
- `updateTicket(string $ticketId, TicketUpdateDto $ticketData): ApiResponseDto`
- `closeTicket(string $ticketId): ApiResponseDto`
- `reopenTicket(string $ticketId): ApiResponseDto`

### Ticket Comments
- `addComment(string $ticketId, CommentDto $comment): ApiResponseDto`

### Ticket Status and Priority
- `updateTicketStatus(string $ticketId, string $status): ApiResponseDto`
- `updateTicketPriority(string $ticketId, string $priority): ApiResponseDto`

## Response Format

All methods return an `ApiResponseDto` object:

```php
$response = Desk365::createTicket($ticketData);

if ($response->isSuccess()) {
    $data = $response->getData();
    $message = $response->getMessage();
} else {
    $errors = $response->getErrors();
    $message = $response->getMessage();
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
