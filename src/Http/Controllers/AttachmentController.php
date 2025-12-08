<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto
};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttachmentController
{
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
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->attach('file', $file, $metadata['filename'] ?? null)
                ->post($this->getEndpoint("tickets/{$ticketId}/attachments"), $metadata);

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Upload Attachment', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to upload attachment: ' . $e->getMessage());
        }
    }

    public function getAll(string $ticketId, array $params = []): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("tickets/{$ticketId}/attachments", $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Attachments', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get attachments: ' . $e->getMessage());
        }
    }

    public function getById(string $ticketId, string $attachmentId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("tickets/{$ticketId}/attachments/{$attachmentId}"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Attachment', ['ticket_id' => $ticketId, 'attachment_id' => $attachmentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get attachment: ' . $e->getMessage());
        }
    }

    public function delete(string $ticketId, string $attachmentId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->delete($this->getEndpoint("tickets/{$ticketId}/attachments/{$attachmentId}"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete Attachment', ['ticket_id' => $ticketId, 'attachment_id' => $attachmentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to delete attachment: ' . $e->getMessage());
        }
    }

    public function download(string $ticketId, string $attachmentId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("tickets/{$ticketId}/attachments/{$attachmentId}/download"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Download Attachment', ['ticket_id' => $ticketId, 'attachment_id' => $attachmentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to download attachment: ' . $e->getMessage());
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



