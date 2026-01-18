# Desk365 API Integration for Laravel

Laravel Package for complete integration with Desk365 Ticketing API. This package provides comprehensive access to all Desk365 API endpoints including Tickets, Agents, Customers, Comments, Attachments, and Reports.

## Installation

You can install the package via composer:

```bash
composer require devmatika/desk365
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
use Devmatika\Desk365\Facades\Desk365;
use Devmatika\Desk365\DTO\{
    TicketCreateDto,
    TicketUpdateDto,
    TicketFilterDto,
    CommentDto,
    AgentDto,
    CustomerDto,
    ApiConfigDto
};

// Initialize with config
$config = new ApiConfigDto(
    baseUrl: config('desk365.base_url'),
    apiKey: config('desk365.api_key'),
    apiSecret: config('desk365.api_secret'),
    timeout: config('desk365.timeout', 30),
    version: config('desk365.version', 'v3')
);

Desk365::init($config);
```

## Available API Methods

### Ticket Operations

#### Get All Tickets
```php
$filters = new TicketFilterDto(
    status: 'open',
    priority: 'high',
    page: 1,
    perPage: 20
);
$response = Desk365::getAllTickets($filters);
```

#### Get Single Ticket
```php
$response = Desk365::getTicket('TKT-001');
```

#### Create Ticket
```php
$ticketData = new TicketCreateDto(
    email: 'customer@example.com',
    subject: 'Support Request',
    description: 'I need help with my account',
    assignedTo: 'agent_1',
    group: 'support',
    category: 'technical',
    status: 'open',
    priority: 1,
    type: 'Question'
);

$response = Desk365::createTicket($ticketData);
```

#### Create Ticket with Attachment
```php
$ticketData = new TicketCreateDto(
    email: 'customer@example.com',
    subject: 'Support Request',
    description: 'I need help',
    assignedTo: 'agent_1',
    group: 'support',
    category: 'technical',
    file: $request->file('attachment')
);

$response = Desk365::createTicket($ticketData);
```

#### Update Ticket
```php
$updateData = new TicketUpdateDto(
    status: 'in_progress',
    priority: 2,
    assignedTo: 'agent_2'
);

$response = Desk365::updateTicket('TKT-001', $updateData);
```

#### Delete Ticket
```php
$response = Desk365::deleteTicket('TKT-001');
```

#### Search Tickets
```php
$filters = new TicketFilterDto(status: 'open');
$response = Desk365::searchTickets('support request', $filters);
```

#### Close/Reopen Ticket
```php
$response = Desk365::closeTicket('TKT-001');
$response = Desk365::reopenTicket('TKT-001');
```

#### Update Ticket Status/Priority
```php
$response = Desk365::updateTicketStatus('TKT-001', 'closed');
$response = Desk365::updateTicketPriority('TKT-001', '3');
```

### Comment Operations

#### Get All Comments
```php
$response = Desk365::getComments('TKT-001', ['page' => 1, 'per_page' => 20]);
```

#### Get Single Comment
```php
$response = Desk365::getComment('TKT-001', 'comment_123');
```

#### Add Comment
```php
$comment = new CommentDto(
    content: 'This is a test comment',
    authorId: 'agent_1',
    authorName: 'John Doe',
    authorType: 'agent',
    isPublic: true
);

$response = Desk365::addComment('TKT-001', $comment);
```

#### Update Comment
```php
$comment = new CommentDto(content: 'Updated comment');
$response = Desk365::updateComment('TKT-001', 'comment_123', $comment);
```

#### Delete Comment
```php
$response = Desk365::deleteComment('TKT-001', 'comment_123');
```

### Attachment Operations

#### Upload Attachment
```php
$response = Desk365::uploadAttachment('TKT-001', $file, [
    'filename' => 'document.pdf',
    'description' => 'Support document'
]);
```

#### Get All Attachments
```php
$response = Desk365::getAttachments('TKT-001');
```

#### Get Single Attachment
```php
$response = Desk365::getAttachment('TKT-001', 'attachment_123');
```

#### Delete Attachment
```php
$response = Desk365::deleteAttachment('TKT-001', 'attachment_123');
```

#### Download Attachment
```php
$response = Desk365::downloadAttachment('TKT-001', 'attachment_123');
```

### Agent Operations

#### Get All Agents
```php
$response = Desk365::getAllAgents(['page' => 1, 'per_page' => 20]);
```

#### Get Single Agent
```php
$response = Desk365::getAgent('agent_123');
```

#### Create Agent
```php
$agentData = new AgentDto(
    name: 'John Doe',
    email: 'john@example.com',
    phone: '+1234567890',
    role: 'agent',
    isActive: true
);

$response = Desk365::createAgent($agentData);
```

#### Update Agent
```php
$agentData = new AgentDto(name: 'Jane Doe', role: 'admin');
$response = Desk365::updateAgent('agent_123', $agentData);
```

#### Delete Agent
```php
$response = Desk365::deleteAgent('agent_123');
```

### Customer Operations

#### Get All Customers
```php
$response = Desk365::getAllCustomers(['page' => 1, 'per_page' => 20]);
```

#### Get Single Customer
```php
$response = Desk365::getCustomer('customer_123');
```

#### Create Customer
```php
$customerData = new CustomerDto(
    name: 'John Customer',
    email: 'customer@example.com',
    phone: '+1234567890',
    company: 'Example Corp'
);

$response = Desk365::createCustomer($customerData);
```

#### Update Customer
```php
$customerData = new CustomerDto(name: 'Jane Customer');
$response = Desk365::updateCustomer('customer_123', $customerData);
```

#### Delete Customer
```php
$response = Desk365::deleteCustomer('customer_123');
```

#### Search Customers
```php
$response = Desk365::searchCustomers('john', ['page' => 1]);
```

### Report Operations

#### Get Ticket Statistics
```php
$filters = new TicketFilterDto(status: 'open', dateFrom: '2024-01-01');
$response = Desk365::getTicketStatistics($filters);
```

#### Get Agent Statistics
```php
$response = Desk365::getAgentStatistics('agent_123', '2024-01-01', '2024-12-31');
```

#### Get Dashboard Data
```php
$response = Desk365::getDashboardData(['date_from' => '2024-01-01']);
```

## Response Format

All methods return an `ApiResponseDto` object:

```php
$response = Desk365::createTicket($ticketData);

if ($response->isSuccess()) {
    $data = $response->getData();
    $message = $response->getMessage();
    $statusCode = $response->getStatusCode();
    $meta = $response->getMeta(); // For pagination, etc.
} else {
    $errors = $response->getErrors();
    $message = $response->getMessage();
    $statusCode = $response->getStatusCode();
}
```

## Error Handling

The package automatically handles errors and returns appropriate error responses:

```php
$response = Desk365::getTicket('invalid_id');

if ($response->isError()) {
    Log::error('Desk365 Error', [
        'message' => $response->getMessage(),
        'errors' => $response->getErrors(),
        'status_code' => $response->getStatusCode()
    ]);
}
```

## Testing

```bash
cd desk365api
composer install
composer test
```

## API Documentation

For complete API documentation, visit: [Desk365 API Docs](https://apps.desk365.io/apis/api-docs.html)

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
