<?php

namespace Devmatika\Desk365\Http\Controllers;

use Devmatika\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto
};
use Devmatika\Desk365\Traits\LogsApiCalls;
use Devmatika\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class KbFolderController
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
     * Get KB folder details by category and folder name.
     */
    public function getDetails(string $categoryName, string $folderName): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/folder/details');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [
                    'category_name' => $categoryName,
                    'folder_name' => $folderName,
                ],
                timeout: $this->config->timeout,
                operation: 'getKbFolder'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get KB Folder', [
                'category_name' => $categoryName,
                'folder_name' => $folderName,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to get KB folder: ' . $e->getMessage());
        }
    }

    /**
     * Create KB folder.
     */
    public function create(array $data): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/folder/create');
            $response = $this->makeLoggedApiCall(
                method: 'POST',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $data,
                timeout: $this->config->timeout,
                operation: 'createKbFolder'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create KB Folder', [
                'payload' => $data,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to create KB folder: ' . $e->getMessage());
        }
    }

    /**
     * Update KB folder.
     */
    public function update(string $categoryName, string $folderName, array $data): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/folder/update', [
                'category_name' => $categoryName,
                'folder_name' => $folderName,
            ]);

            $response = $this->makeLoggedApiCall(
                method: 'PUT',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $data,
                timeout: $this->config->timeout,
                operation: 'updateKbFolder'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update KB Folder', [
                'category_name' => $categoryName,
                'folder_name' => $folderName,
                'payload' => $data,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to update KB folder: ' . $e->getMessage());
        }
    }

    /**
     * Delete KB folder.
     */
    public function delete(string $categoryName, string $folderName): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('kb/folder/delete');
            $response = $this->makeLoggedApiCall(
                method: 'DELETE',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [
                    'category_name' => $categoryName,
                    'folder_name' => $folderName,
                ],
                timeout: $this->config->timeout,
                operation: 'deleteKbFolder'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete KB Folder', [
                'category_name' => $categoryName,
                'folder_name' => $folderName,
                'error' => $e->getMessage(),
            ]);

            return ApiResponseDto::error('Failed to delete KB folder: ' . $e->getMessage());
        }
    }
}

