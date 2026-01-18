<?php

namespace Devmatika\Desk365\Http\Controllers;

use Devmatika\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    TicketCreateDto,
    TicketUpdateDto,
    TicketFilterDto,
    TicketResponseDto,
    ReplyDto,
    NoteDto
};
use Devmatika\Desk365\Traits\LogsApiCalls;
use Devmatika\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class TicketController
{
    use LogsApiCalls, HandlesApiResponses;
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

    /**
     * Add a reply to a ticket, optionally with attachments
     * 
     * Attachments follow Desk365 API rules:
     * - Content-Type is automatically set to 'multipart/form-data'
     * - Multiple files use 'files' parameter, single file uses 'file' parameter
     * - Only local files can be attached
     * 
     * @param string $ticketNumber The ticket number
     * @param ReplyDto $replyData The reply data
     * @param mixed $files File path(s) - can be string (single file) or array (multiple files), or null for no attachments
     * @return ApiResponseDto
     */
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

    /**
     * Add a note to a ticket, optionally with attachments
     * 
     * Attachments follow Desk365 API rules:
     * - Content-Type is automatically set to 'multipart/form-data'
     * - Multiple files use 'files' parameter, single file uses 'file' parameter
     * - Only local files can be attached
     * 
     * @param string $ticketNumber The ticket number
     * @param NoteDto $noteData The note data
     * @param mixed $files File path(s) - can be string (single file) or array (multiple files), or null for no attachments
     * @return ApiResponseDto
     */
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

}



