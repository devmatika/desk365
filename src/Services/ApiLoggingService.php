<?php

namespace Davoodf1995\Desk365\Services;

use Davoodf1995\Desk365\Models\Desk365ApiLog;
use Illuminate\Support\Facades\Log;

class ApiLoggingService
{
    /**
     * Log API call to database
     */
    public function log(
        string $method,
        string $endpoint,
        ?array $requestHeaders,
        ?string $requestBody,
        ?int $responseStatus,
        ?string $responseBody,
        ?int $durationMs,
        ?string $operation,
        ?string $errorMessage
    ): void {
        try {
            // Check if the model table exists before trying to log
            if (!$this->tableExists()) {
                Log::debug('Desk365 API Log table does not exist, skipping database log', [
                    'endpoint' => $endpoint,
                    'method' => $method,
                ]);
                return;
            }

            Desk365ApiLog::create([
                'method' => $method,
                'endpoint' => $endpoint,
                'request_headers' => $requestHeaders,
                'request_body' => $requestBody,
                'response_status' => $responseStatus,
                'response_body' => $responseBody,
                'duration_ms' => $durationMs,
                'operation' => $operation,
                'error_message' => $errorMessage,
            ]);
        } catch (\Exception $e) {
            // Log to Laravel log if database logging fails
            Log::error('Failed to log Desk365 API call to database', [
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'method' => $method,
            ]);
        }
    }

    /**
     * Sanitize headers to remove sensitive information
     */
    public function sanitizeHeaders(array $headers): array
    {
        $sanitized = $headers;
        
        // Remove or mask sensitive headers
        $sensitiveKeys = ['api-key', 'api_secret', 'authorization', 'x-api-key', 'x-api-secret'];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($sanitized[$key])) {
                $sanitized[$key] = '***REDACTED***';
            }
            // Also check case-insensitive
            foreach ($sanitized as $headerKey => $value) {
                if (strtolower($headerKey) === strtolower($key)) {
                    $sanitized[$headerKey] = '***REDACTED***';
                }
            }
        }
        
        return $sanitized;
    }

    /**
     * Check if the desk365_api_logs table exists
     */
    private function tableExists(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('desk365_api_logs');
        } catch (\Exception $e) {
            return false;
        }
    }
}

