<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    TicketCreateDto,
    TicketUpdateDto,
    TicketFilterDto,
    TicketResponseDto,
    ReplyDto,
    NoteDto
};
use Davoodf1995\Desk365\Traits\LogsApiCalls;
use Illuminate\Support\Facades\Log;

class TicketController
{
    use LogsApiCalls;
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    public function getAll(?TicketFilterDto $filters = null): ApiResponseDto
    {
        try {
            $params = $filters ? $filters->toArray() : [];
            $endpoint = $this->getEndpoint('tickets');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getAllTickets'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get All Tickets', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get tickets: ' . $e->getMessage());
        }
    }

    public function getById(string $ticketNumber): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("tickets/details");
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: ['ticket_number' => $ticketNumber],
                timeout: $this->config->timeout,
                operation: 'getTicket'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Ticket', ['ticket_number' => $ticketNumber, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get ticket: ' . $e->getMessage());
        }
    }

    public function create(TicketCreateDto $ticketData): ApiResponseDto
    {
        try {
            $files = $ticketData->file ?? null;
            if($files == null){
                $endpoint = $this->getEndpoint('tickets/create');
                $response = $this->makeLoggedApiCall(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: $ticketData->toArray(),
                    timeout: $this->config->timeout,
                    operation: 'createTicket'
                );

                return $this->handleResponse($response);
            } else {
                $ticketArray = $ticketData->toArray();
                unset($ticketArray['file']);
                $ticketObject = json_encode($ticketArray);
                $endpoint = $this->getEndpoint('tickets/create_with_attachment', ['ticket_object' => $ticketObject]);

                $response = $this->makeLoggedApiCallWithFile(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: ['ticket_object' => $ticketObject],
                    file: $files,
                    timeout: $this->config->timeout,
                    operation: 'createTicket'
                );

                return $this->handleResponse($response);
            }
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create Ticket', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to create ticket: ' . $e->getMessage());
        }
    }

    public function update(string $ticketNumber, TicketUpdateDto $ticketData): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("tickets/update", ['ticket_number' => $ticketNumber]);
            $response = $this->makeLoggedApiCall(
                method: 'PUT',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $ticketData->toArray(),
                timeout: $this->config->timeout,
                operation: 'updateTicket'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update Ticket', ['ticket_number' => $ticketNumber, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to update ticket: ' . $e->getMessage());
        }
    }

    public function getConversations(string $ticketNumber, array $params = []): ApiResponseDto
    {
        try {
            $params['ticket_number'] = $ticketNumber;
            $endpoint = $this->getEndpoint("tickets/conversations");
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getTicketConversations'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Ticket Conversations', ['ticket_number' => $ticketNumber, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get ticket conversations: ' . $e->getMessage());
        }
    }

    public function addReply(string $ticketNumber, ReplyDto $replyData, $files = null): ApiResponseDto
    {
        try {
            if ($files === null) {
                $endpoint = $this->getEndpoint("tickets/add_reply", ['ticket_number' => $ticketNumber]);
                $response = $this->makeLoggedApiCall(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: $replyData->toArray(),
                    timeout: $this->config->timeout,
                    operation: 'addReply'
                );

                return $this->handleResponse($response);
            } else {
                $replyObject = json_encode($replyData->toArray());
                $endpoint = $this->getEndpoint("tickets/add_reply_with_attachment", [
                    'ticket_number' => $ticketNumber,
                    'reply_object' => $replyObject
                ]);

                $response = $this->makeLoggedApiCallWithFile(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: ['reply_object' => $replyObject],
                    file: $files,
                    timeout: $this->config->timeout,
                    operation: 'addReply'
                );

                return $this->handleResponse($response);
            }
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Add Reply', ['ticket_number' => $ticketNumber, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to add reply: ' . $e->getMessage());
        }
    }

    public function addNote(string $ticketNumber, NoteDto $noteData, $files = null): ApiResponseDto
    {
        try {
            if ($files === null) {
                $endpoint = $this->getEndpoint("tickets/add_note", ['ticket_number' => $ticketNumber]);
                $response = $this->makeLoggedApiCall(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: $noteData->toArray(),
                    timeout: $this->config->timeout,
                    operation: 'addNote'
                );

                return $this->handleResponse($response);
            } else {
                $noteObject = json_encode($noteData->toArray());
                $endpoint = $this->getEndpoint("tickets/add_note_with_attachment", [
                    'ticket_number' => $ticketNumber,
                    'note_object' => $noteObject
                ]);

                $response = $this->makeLoggedApiCallWithFile(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: ['note_object' => $noteObject],
                    file: $files,
                    timeout: $this->config->timeout,
                    operation: 'addNote'
                );

                return $this->handleResponse($response);
            }
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Add Note', ['ticket_number' => $ticketNumber, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to add note: ' . $e->getMessage());
        }
    }

    private function getEndpoint(string $path, array $parameters = []): string
    {
        $endpoint = rtrim($this->config->baseUrl, '/') . '/apis/' . $this->apiVersion . '/' . ltrim($path, '/');
        if (!empty($parameters)) {
            $query = http_build_query($parameters);
            $endpoint .= '?' . $query;
        }
        return $endpoint;
    }

    private function handleResponse($response): ApiResponseDto
    {
        $statusCode = $response->status();
        $data = $response->json();

        if ($response->successful()) {
            return ApiResponseDto::success(
                data: $data['data'] ?? $data,
                message: $data['message'] ?? null,
                statusCode: $statusCode,
                meta: $data['meta'] ?? null
            );
        }

        // Try to extract error message from various possible fields
        $errorMessage = $data['message'] ?? $data['error'] ?? null;
        if (!is_string($errorMessage)) {
            $errorMessage = is_array($data) ? json_encode($data) : 'API request failed';
        }

        return ApiResponseDto::error(
            message: $errorMessage,
            errors: $data['errors'] ?? null,
            statusCode: $statusCode,
            meta: $data['meta'] ?? null
        );
    }
}



