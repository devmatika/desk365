<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    ReplyDto,
    NoteDto
};
use Davoodf1995\Desk365\Traits\LogsApiCalls;
use Davoodf1995\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class CommentController
{
    use LogsApiCalls, HandlesApiResponses;
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    public function getAll(string $ticketNumber, array $params = []): ApiResponseDto
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
                operation: 'getConversations'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Conversations', ['ticket_number' => $ticketNumber, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get conversations: ' . $e->getMessage());
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

}



