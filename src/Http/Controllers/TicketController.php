<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    TicketCreateDto,
    TicketUpdateDto,
    TicketFilterDto,
    TicketResponseDto
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

    public function getById(string $ticketId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("tickets/{$ticketId}"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Ticket', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get ticket: ' . $e->getMessage());
        }
    }

    public function create(TicketCreateDto $ticketData): ApiResponseDto
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

    public function update(string $ticketId, TicketUpdateDto $ticketData): ApiResponseDto
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

    public function delete(string $ticketId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->delete($this->getEndpoint("tickets/{$ticketId}"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete Ticket', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to delete ticket: ' . $e->getMessage());
        }
    }

    public function search(string $query, ?TicketFilterDto $filters = null): ApiResponseDto
    {
        try {
            $params = $filters ? $filters->toArray() : [];
            $params['search'] = $query;
            
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint('tickets/search', $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Search Tickets', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to search tickets: ' . $e->getMessage());
        }
    }

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



