<?php

namespace Devmatika\Desk365\Traits;

use Devmatika\Desk365\DTO\ApiResponseDto;
use Devmatika\Desk365\DTO\ApiConfigDto;

trait HandlesApiResponses
{
    /**
     * Build API endpoint URL
     * Requires $this->config and $this->apiVersion properties in the using class
     */
    protected function getEndpoint(string $path, array $parameters = []): string
    {
        /** @var ApiConfigDto $config */
        $config = $this->config ?? null;
        $apiVersion = $this->apiVersion ?? 'v3';
        
        $baseUrl = $config?->baseUrl ?? config('desk365.base_url', '');
        $endpoint = rtrim($baseUrl, '/') . '/apis/' . $apiVersion . '/' . ltrim($path, '/');
        
        if (!empty($parameters)) {
            $query = http_build_query($parameters);
            $endpoint .= '?' . $query;
        }
        
        return $endpoint;
    }

    /**
     * Handle API response and convert to ApiResponseDto
     * Properly handles error messages from various API response formats
     */
    protected function handleResponse($response): ApiResponseDto
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

        // Try to extract error message from various possible fields
        $errorMessage = $data['message'] ?? $data['error'] ?? $data['description'] ?? $data;
        
        // Ensure error message is always a string
        if (!is_string($errorMessage)) {
            $errorMessage = is_array($data) ? json_encode($data) : 'API request failed';
        }

        return ApiResponseDto::error(
            message: $errorMessage,
            errors: $data['errors'] ?? null,
            statusCode: $statusCode,
            meta: $data['meta'] ?? null
        );
    }
}

