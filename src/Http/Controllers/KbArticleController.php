<?php

namespace Devmatika\Desk365\Http\Controllers;

use Devmatika\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto
};
use Devmatika\Desk365\Traits\LogsApiCalls;
use Devmatika\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class KbArticleController
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
     * List all live KB article titles.
     *
     * Maps to GET /v3/kb/article/
     */
    public function getAll(): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/article/');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [],
                timeout: $this->config->timeout,
                operation: 'getAllKbArticles'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get All KB Articles', [
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to get KB articles: ' . $e->getMessage());
        }
    }

    /**
     * Get KB article details by article name.
     *
     * Maps to GET /v3/kb/article/details
     */
    public function getDetails(string $articleName): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/article/details');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: ['article_name' => $articleName],
                timeout: $this->config->timeout,
                operation: 'getKbArticle'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get KB Article', [
                'article_name' => $articleName,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to get KB article: ' . $e->getMessage());
        }
    }

    /**
     * Create KB article.
     *
     * Maps to POST /v3/kb/article/create
     * Expects payload similar to CreateKBArticleRequestModel.
     */
    public function create(array $data): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/article/create');
            $response = $this->makeLoggedApiCall(
                method: 'POST',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $data,
                timeout: $this->config->timeout,
                operation: 'createKbArticle'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create KB Article', [
                'payload' => $data,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to create KB article: ' . $e->getMessage());
        }
    }

    /**
     * Update KB article.
     *
     * Maps to PUT /v3/kb/article/update
     */
    public function update(string $articleName, array $data): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/article/update', [
                'article_name' => $articleName,
            ]);

            $response = $this->makeLoggedApiCall(
                method: 'PUT',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $data,
                timeout: $this->config->timeout,
                operation: 'updateKbArticle'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update KB Article', [
                'article_name' => $articleName,
                'payload' => $data,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to update KB article: ' . $e->getMessage());
        }
    }

    /**
     * Delete KB article.
     *
     * Maps to DELETE /v3/kb/article/delete
     */
    public function delete(string $articleName): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/article/delete');
            $response = $this->makeLoggedApiCall(
                method: 'DELETE',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: ['article_name' => $articleName],
                timeout: $this->config->timeout,
                operation: 'deleteKbArticle'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete KB Article', [
                'article_name' => $articleName,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to delete KB article: ' . $e->getMessage());
        }
    }
}

