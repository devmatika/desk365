<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    TicketFilterDto
};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReportController
{
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    public function getTicketStatistics(?TicketFilterDto $filters = null): ApiResponseDto
    {
        try {
            $params = $filters ? $filters->toArray() : [];
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint('reports/tickets/statistics', $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Ticket Statistics', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get ticket statistics: ' . $e->getMessage());
        }
    }

    public function getAgentStatistics(string $agentId, ?string $dateFrom = null, ?string $dateTo = null): ApiResponseDto
    {
        try {
            $params = array_filter([
                'agent_id' => $agentId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
            
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint('reports/agents/statistics', $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Agent Statistics', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get agent statistics: ' . $e->getMessage());
        }
    }

    public function getDashboardData(array $params = []): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint('reports/dashboard', $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Dashboard Data', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get dashboard data: ' . $e->getMessage());
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



