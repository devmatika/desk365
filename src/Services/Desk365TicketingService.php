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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Desk365TicketingService implements TicketingServiceInterface
{
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    // Ticket Operations
    public function createTicket(TicketCreateDto $ticketData): ApiResponseDto
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

    public function updateTicket(string $ticketNumber, TicketUpdateDto $ticketData): ApiResponseDto
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
    public function addReply(string $ticketNumber, ReplyDto $reply, $files = null): ApiResponseDto
    {
        try {
            if ($files === null) {
                $response = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout)
                    ->post($this->getEndpoint("tickets/add_reply", ['ticket_number' => $ticketNumber]), $reply->toArray());

                return $this->handleResponse($response);
            } else {
                $replyObject = json_encode($reply->toArray());
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

    // Ticket Notes
    public function addNote(string $ticketNumber, NoteDto $note, $files = null): ApiResponseDto
    {
        try {
            if ($files === null) {
                $response = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout)
                    ->post($this->getEndpoint("tickets/add_note", ['ticket_number' => $ticketNumber]), $note->toArray());

                return $this->handleResponse($response);
            } else {
                $noteObject = json_encode($note->toArray());
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



