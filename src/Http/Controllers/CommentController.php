<?php

namespace Davoodf1995\Desk365\Http\Controllers;

use Davoodf1995\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    CommentDto
};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CommentController
{
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    public function getAll(string $ticketId, array $params = []): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("tickets/{$ticketId}/comments", $params));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Comments', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get comments: ' . $e->getMessage());
        }
    }

    public function getById(string $ticketId, string $commentId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->get($this->getEndpoint("tickets/{$ticketId}/comments/{$commentId}"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Comment', ['ticket_id' => $ticketId, 'comment_id' => $commentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get comment: ' . $e->getMessage());
        }
    }

    public function add(string $ticketId, CommentDto $comment): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->post($this->getEndpoint("tickets/add_reply"), array_merge($comment->toArray(), ['ticket_id' => $ticketId]));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Add Comment', ['ticket_id' => $ticketId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to add comment: ' . $e->getMessage());
        }
    }

    public function update(string $ticketId, string $commentId, CommentDto $comment): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->put($this->getEndpoint("tickets/{$ticketId}/comments/{$commentId}"), $comment->toArray());

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update Comment', ['ticket_id' => $ticketId, 'comment_id' => $commentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to update comment: ' . $e->getMessage());
        }
    }

    public function delete(string $ticketId, string $commentId): ApiResponseDto
    {
        try {
            $response = Http::withHeaders($this->config->getAuthHeaders())
                ->timeout($this->config->timeout)
                ->delete($this->getEndpoint("tickets/{$ticketId}/comments/{$commentId}"));

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete Comment', ['ticket_id' => $ticketId, 'comment_id' => $commentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to delete comment: ' . $e->getMessage());
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



