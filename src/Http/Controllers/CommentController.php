<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    ReplyDto,
    NoteDto
};
use Davoodf1995\Desk365\Traits\LogsApiCalls;
use Illuminate\Support\Facades\Log;

class CommentController
{
    use LogsApiCalls;
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

        return ApiResponseDto::error(
            message: $data['message'] ?? 'API request failed',
            errors: $data['errors'] ?? null,
            statusCode: $statusCode,
            meta: $data['meta'] ?? null
        );
    }
}



