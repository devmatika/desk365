<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto
};
use Davoodf1995\Desk365\Traits\LogsApiCalls;
use Illuminate\Support\Facades\Log;

class AttachmentController
{
    use LogsApiCalls;
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    public function upload(string $ticketId, $file, array $metadata = []): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("tickets/{$ticketId}/attachments");
            $response = $this->makeLoggedApiCallWithFile(
                method: 'POST',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $metadata,
                file: $file,
                timeout: $this->config->timeout,
                operation: 'uploadAttachment'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Upload Attachment', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to upload attachment: ' . $e->getMessage());
        }
    }

    public function getAll(string $ticketId, array $params = []): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("tickets/{$ticketId}/attachments", $params);
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getAttachments'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Attachments', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get attachments: ' . $e->getMessage());
        }
    }

    public function getById(string $ticketId, string $attachmentId): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("tickets/{$ticketId}/attachments/{$attachmentId}");
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [],
                timeout: $this->config->timeout,
                operation: 'getAttachment'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Attachment', ['ticket_id' => $ticketId, 'attachment_id' => $attachmentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get attachment: ' . $e->getMessage());
        }
    }

    public function delete(string $ticketId, string $attachmentId): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("tickets/{$ticketId}/attachments/{$attachmentId}");
            $response = $this->makeLoggedApiCall(
                method: 'DELETE',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [],
                timeout: $this->config->timeout,
                operation: 'deleteAttachment'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete Attachment', ['ticket_id' => $ticketId, 'attachment_id' => $attachmentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to delete attachment: ' . $e->getMessage());
        }
    }

    public function download(string $ticketId, string $attachmentId): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("tickets/{$ticketId}/attachments/{$attachmentId}/download");
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [],
                timeout: $this->config->timeout,
                operation: 'downloadAttachment'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Download Attachment', ['ticket_id' => $ticketId, 'attachment_id' => $attachmentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to download attachment: ' . $e->getMessage());
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



