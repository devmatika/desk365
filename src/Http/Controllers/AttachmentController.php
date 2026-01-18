<?php

namespace Devmatika\Desk365\Http\Controllers;

use Devmatika\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto
};
use Devmatika\Desk365\Traits\LogsApiCalls;
use Devmatika\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class AttachmentController
{
    use LogsApiCalls, HandlesApiResponses;
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
            $endpoint = $this->getEndpoint("tickets/{$ticketId}/attachments");
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

}



