<?php

namespace Davoodf1995\Desk365\Services;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    TicketCreateDto,
    TicketUpdateDto,
    ReplyDto,
    NoteDto
};
use Davoodf1995\Desk365\Traits\LogsApiCalls;
use Illuminate\Support\Facades\Log;

class Desk365TicketingService implements TicketingServiceInterface
{
    use LogsApiCalls;
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    // Ticket Operations
    /**
     * Create a new ticket, optionally with attachments
     * 
     * Attachments follow Desk365 API rules:
     * - Content-Type is automatically set to 'multipart/form-data'
     * - Multiple files use 'files' parameter, single file uses 'file' parameter
     * - Only local files can be attached
     * 
     * @param TicketCreateDto $ticketData The ticket data (may include 'file' property for attachments)
     * @return ApiResponseDto
     */
    public function createTicket(TicketCreateDto $ticketData): ApiResponseDto
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
                $ticketObject = json_encode($ticketArray);
                $endpoint = $this->getEndpoint('tickets/create_with_attachment', ['ticket_object' => $ticketObject]);

                // Uses makeLoggedApiCallWithFile which handles:
                // - Content-Type: multipart/form-data (automatic)
                // - Multiple files: 'files' parameter
                // - Single file: 'file' parameter
                $response = $this->makeLoggedApiCallWithFile(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: [], // Parameters are in query string, not form data
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

    public function updateTicket(string $ticketNumber, TicketUpdateDto $ticketData): ApiResponseDto
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

    public function closeTicket(string $ticketNumber): ApiResponseDto
    {
        $updateData = new TicketUpdateDto(status: 'closed');
        return $this->updateTicket($ticketNumber, $updateData);
    }

    public function reopenTicket(string $ticketNumber): ApiResponseDto
    {
        $updateData = new TicketUpdateDto(status: 'open');
        return $this->updateTicket($ticketNumber, $updateData);
    }

    // Ticket Replies
    /**
     * Add a reply to a ticket, optionally with attachments
     * 
     * Attachments follow Desk365 API rules:
     * - Content-Type is automatically set to 'multipart/form-data'
     * - Multiple files use 'files' parameter, single file uses 'file' parameter
     * - Only local files can be attached
     * 
     * @param string $ticketNumber The ticket number
     * @param ReplyDto $reply The reply data
     * @param mixed $files File path(s) - can be string (single file) or array (multiple files), or null for no attachments
     * @return ApiResponseDto
     */
    public function addReply(string $ticketNumber, ReplyDto $reply, $files = null): ApiResponseDto
    {
        try {
            if ($files === null) {
                $endpoint = $this->getEndpoint("tickets/add_reply", ['ticket_number' => $ticketNumber]);
                $response = $this->makeLoggedApiCall(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: $reply->toArray(),
                    timeout: $this->config->timeout,
                    operation: 'addReply'
                );

                return $this->handleResponse($response);
            } else {
                $replyObject = json_encode($reply->toArray());
                $endpoint = $this->getEndpoint("tickets/add_reply_with_attachment", [
                    'ticket_number' => $ticketNumber,
                    'reply_object' => $replyObject
                ]);

                // Uses makeLoggedApiCallWithFile which handles:
                // - Content-Type: multipart/form-data (automatic)
                // - Multiple files: 'files' parameter
                // - Single file: 'file' parameter
                $response = $this->makeLoggedApiCallWithFile(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: [], // Parameters are in query string, not form data
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

    // Ticket Notes
    /**
     * Add a note to a ticket, optionally with attachments
     * 
     * Attachments follow Desk365 API rules:
     * - Content-Type is automatically set to 'multipart/form-data'
     * - Multiple files use 'files' parameter, single file uses 'file' parameter
     * - Only local files can be attached
     * 
     * @param string $ticketNumber The ticket number
     * @param NoteDto $note The note data
     * @param mixed $files File path(s) - can be string (single file) or array (multiple files), or null for no attachments
     * @return ApiResponseDto
     */
    public function addNote(string $ticketNumber, NoteDto $note, $files = null): ApiResponseDto
    {
        try {
            if ($files === null) {
                $endpoint = $this->getEndpoint("tickets/add_note", ['ticket_number' => $ticketNumber]);
                $response = $this->makeLoggedApiCall(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: $note->toArray(),
                    timeout: $this->config->timeout,
                    operation: 'addNote'
                );

                return $this->handleResponse($response);
            } else {
                $noteObject = json_encode($note->toArray());
                $endpoint = $this->getEndpoint("tickets/add_note_with_attachment", [
                    'ticket_number' => $ticketNumber,
                    'note_object' => $noteObject
                ]);

                // Uses makeLoggedApiCallWithFile which handles:
                // - Content-Type: multipart/form-data (automatic)
                // - Multiple files: 'files' parameter
                // - Single file: 'file' parameter
                $response = $this->makeLoggedApiCallWithFile(
                    method: 'POST',
                    endpoint: $endpoint,
                    headers: $this->config->getAuthHeaders(),
                    data: [], // Parameters are in query string, not form data
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

    // Ticket Status and Priority
    public function updateTicketStatus(string $ticketNumber, string $status): ApiResponseDto
    {
        $updateData = new TicketUpdateDto(status: $status);
        return $this->updateTicket($ticketNumber, $updateData);
    }

    public function updateTicketPriority(string $ticketNumber, int $priority): ApiResponseDto
    {
        $updateData = new TicketUpdateDto(priority: $priority);
        return $this->updateTicket($ticketNumber, $updateData);
    }

    // Helper Methods
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

        return ApiResponseDto::error(
            message: $data['message'] ?? 'API request failed',
            errors: $data['errors'] ?? null,
            statusCode: $statusCode,
            meta: $data['meta'] ?? null
        );
    }
}



