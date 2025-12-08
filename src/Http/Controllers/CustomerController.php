<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    CustomerDto
};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerController
{
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    public function getAll(array $params = []): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint('customers', $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get All Customers', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get customers: ' . $e->getMessage());
        }
    }

    public function getById(string $customerId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("customers/{$customerId}"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Customer', ['customer_id' => $customerId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get customer: ' . $e->getMessage());
        }
    }

    public function create(CustomerDto $customerData): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->post($this->getEndpoint('customers'), $customerData->toArray());

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create Customer', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to create customer: ' . $e->getMessage());
        }
    }

    public function update(string $customerId, CustomerDto $customerData): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->put($this->getEndpoint("customers/{$customerId}"), $customerData->toArray());

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update Customer', ['customer_id' => $customerId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to update customer: ' . $e->getMessage());
        }
    }

    public function delete(string $customerId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->delete($this->getEndpoint("customers/{$customerId}"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete Customer', ['customer_id' => $customerId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to delete customer: ' . $e->getMessage());
        }
    }

    public function search(string $query, array $params = []): ApiResponseDto
    {
        try {
            $params['search'] = $query;
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint('customers/search', $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Search Customers', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to search customers: ' . $e->getMessage());
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



