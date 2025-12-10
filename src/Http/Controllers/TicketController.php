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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TicketController
{
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
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint('tickets', $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get All Tickets', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get tickets: ' . $e->getMessage());
        }
    }

    public function getById(string $ticketNumber): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("tickets/details", ['ticket_number' => $ticketNumber]));

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
                $response = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout)
                    ->post($this->getEndpoint('tickets/create'), $ticketData->toArray());

                return $this->handleResponse($response);
            } else {
                $ticketArray = $ticketData->toArray();
                unset($ticketArray['file']);
                $ticketObject = json_encode($ticketArray);

                $http = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout);

                // Handle multiple files (use 'files' for multiple, 'file' for single)
                if (is_array($files) && count($files) > 1) {
                    foreach ($files as $file) {
                        $http->attach('files', $file);
                    }
                } else {
                    $fileToAttach = is_array($files) ? $files[0] : $files;
                    $http->attach('file', $fileToAttach);
                }

                $response = $http->post($this->getEndpoint('tickets/create_with_attachment', ['ticket_object' => $ticketObject]));

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
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->put($this->getEndpoint("tickets/update", ['ticket_number' => $ticketNumber]), $ticketData->toArray());

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
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("tickets/conversations", $params));

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
                $response = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout)
                    ->post($this->getEndpoint("tickets/add_reply", ['ticket_number' => $ticketNumber]), $replyData->toArray());

                return $this->handleResponse($response);
            } else {
                $replyObject = json_encode($replyData->toArray());
                $http = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout);

                // Handle multiple files (use 'files' for multiple, 'file' for single)
                if (is_array($files) && count($files) > 1) {
                    foreach ($files as $file) {
                        $http->attach('files', $file);
                    }
                } else {
                    $fileToAttach = is_array($files) ? $files[0] : $files;
                    $http->attach('file', $fileToAttach);
                }

                $response = $http->post($this->getEndpoint("tickets/add_reply_with_attachment", [
                    'ticket_number' => $ticketNumber,
                    'reply_object' => $replyObject
                ]));

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
                $response = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout)
                    ->post($this->getEndpoint("tickets/add_note", ['ticket_number' => $ticketNumber]), $noteData->toArray());

                return $this->handleResponse($response);
            } else {
                $noteObject = json_encode($noteData->toArray());
                $http = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout);

                // Handle multiple files (use 'files' for multiple, 'file' for single)
                if (is_array($files) && count($files) > 1) {
                    foreach ($files as $file) {
                        $http->attach('files', $file);
                    }
                } else {
                    $fileToAttach = is_array($files) ? $files[0] : $files;
                    $http->attach('file', $fileToAttach);
                }

                $response = $http->post($this->getEndpoint("tickets/add_note_with_attachment", [
                    'ticket_number' => $ticketNumber,
                    'note_object' => $noteObject
                ]));

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



