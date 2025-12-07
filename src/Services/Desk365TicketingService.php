<?php

namespace Davoodf1995\Desk365\Services;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    TicketCreateDto,
    TicketUpdateDto,
    CommentDto
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
            if($ticketData->file == null){
                $response = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout)
                    ->post($this->getEndpoint('tickets/create'), $ticketData->toArray());

                return $this->handleResponse($response);
            } else {
                $object = json_encode($ticketData->except(['file'])->toArray());

                $response = Http::withHeaders($this->config->getAuthHeaders())
                    ->timeout($this->config->timeout)
                    ->attach('file', $ticketData->file)
                    ->post($this->getEndpoint('tickets/create_with_attachment', ['ticket_object' => $object]));

                return $this->handleResponse($response);
            }
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create Ticket', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to create ticket: ' . $e->getMessage());
        }
    }

    public function updateTicket(string $ticketId, TicketUpdateDto $ticketData): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->put($this->getEndpoint("tickets/update", ['ticket_number' => $ticketId]), $ticketData->toArray());

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update Ticket', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to update ticket: ' . $e->getMessage());
        }
    }

    public function closeTicket(string $ticketId): ApiResponseDto
    {
        $updateData = new TicketUpdateDto(status: 'closed');
        return $this->updateTicket($ticketId, $updateData);
    }

    public function reopenTicket(string $ticketId): ApiResponseDto
    {
        $updateData = new TicketUpdateDto(status: 'open');
        return $this->updateTicket($ticketId, $updateData);
    }

    // Ticket Comments/Messages
    public function addComment(string $ticketId, CommentDto $comment): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->post($this->getEndpoint("tickets/add_reply"), $comment->toArray());

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Add Comment', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to add comment: ' . $e->getMessage());
        }
    }

    // Ticket Status and Priority
    public function updateTicketStatus(string $ticketId, string $status): ApiResponseDto
    {
        $updateData = new TicketUpdateDto(status: $status);
        return $this->updateTicket($ticketId, $updateData);
    }

    public function updateTicketPriority(string $ticketId, string $priority): ApiResponseDto
    {
        $updateData = new TicketUpdateDto(priority: $priority);
        return $this->updateTicket($ticketId, $updateData);
    }

    // Helper Methods
    private function getEndpoint(string $path, array $parameters = []): string
    {
        $endpoint = rtrim($this->config->baseUrl, '/') . '/api/' . $this->apiVersion . '/' . ltrim($path, '/');
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

