<?php

namespace Devmatika\Desk365\Http\Controllers;

use Devmatika\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto
};
use Devmatika\Desk365\Traits\LogsApiCalls;
use Devmatika\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class KbCategoryController
{
    use LogsApiCalls, HandlesApiResponses;

    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    /**
     * List all KB categories (Desk365 does not expose a direct list endpoint for categories in v3,
     * so this method is a placeholder to be extended when needed).
     */
    public function getAll(array $params = []): ApiResponseDto
    {
        // For now, there is no direct /kb/category list endpoint in v3 API spec.
        // We return an empty successful response to keep API consistent.
        return ApiResponseDto::success(data: [], message: 'Listing KB categories is not supported by Desk365 API v3 yet.');
    }

    /**
     * Get single KB category details by name.
     */
    public function getByName(string $categoryName): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/category/details');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: ['category_name' => $categoryName],
                timeout: $this->config->timeout,
                operation: 'getKbCategory'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get KB Category', [
                'category_name' => $categoryName,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to get KB category: ' . $e->getMessage());
        }
    }

    /**
     * Create KB category.
     * Expects payload similar to CreateKBCategoryRequestModel in Desk365 API.
     */
    public function create(array $data): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/category/create');
            $response = $this->makeLoggedApiCall(
                method: 'POST',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $data,
                timeout: $this->config->timeout,
                operation: 'createKbCategory'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create KB Category', [
                'payload' => $data,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to create KB category: ' . $e->getMessage());
        }
    }

    /**
     * Update KB category.
     * Maps to /v3/kb/category/update.
     */
    public function update(string $categoryName, array $data): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/category/update', [
                'category_name' => $categoryName,
            ]);

            $response = $this->makeLoggedApiCall(
                method: 'PUT',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $data,
                timeout: $this->config->timeout,
                operation: 'updateKbCategory'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update KB Category', [
                'category_name' => $categoryName,
                'payload' => $data,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to update KB category: ' . $e->getMessage());
        }
    }

    /**
     * Delete KB category.
     */
    public function delete(string $categoryName): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/category/delete');
            $response = $this->makeLoggedApiCall(
                method: 'DELETE',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: ['category_name' => $categoryName],
                timeout: $this->config->timeout,
                operation: 'deleteKbCategory'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete KB Category', [
                'category_name' => $categoryName,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to delete KB category: ' . $e->getMessage());
        }
    }
}

