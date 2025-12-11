<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    TicketFilterDto
};
use Davoodf1995\Desk365\Traits\LogsApiCalls;
use Illuminate\Support\Facades\Log;

class ReportController
{
    use LogsApiCalls;
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
            $endpoint = $this->getEndpoint('reports/tickets/statistics');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getTicketStatistics'
            );

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
            
            $endpoint = $this->getEndpoint('reports/agents/statistics');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getAgentStatistics'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Agent Statistics', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get agent statistics: ' . $e->getMessage());
        }
    }

    public function getDashboardData(array $params = []): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('reports/dashboard');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getDashboardData'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Dashboard Data', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get dashboard data: ' . $e->getMessage());
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



