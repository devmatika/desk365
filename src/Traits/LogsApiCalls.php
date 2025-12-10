<?php

namespace Davoodf1995\Desk365\Traits;

use Davoodf1995\Desk365\Services\ApiLoggingService;
use Illuminate\Support\Facades\Http;

trait LogsApiCalls
{
    private ?ApiLoggingService $loggingService = null;

    /**
     * Get the logging service instance
     */
    protected function getLoggingService(): ApiLoggingService
    {
        if ($this->loggingService === null) {
            $this->loggingService = new ApiLoggingService();
        }
        return $this->loggingService;
    }

    /**
     * Make an API call and log the request and response
     */
    protected function makeLoggedApiCall(
        string $method,
        string $endpoint,
        array $headers,
        array $data = [],
        int $timeout = 30,
        ?string $operation = null
    ): \Illuminate\Http\Client\Response {
        $startTime = microtime(true);
        $sanitizedHeaders = $this->getLoggingService()->sanitizeHeaders($headers);
        $requestBody = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;

        try {
            $httpClient = Http::withHeaders($headers)->timeout($timeout);
            
            $response = match(strtoupper($method)) {
                'GET' => $httpClient->get($endpoint, $data),
                'POST' => $httpClient->post($endpoint, $data),
                'PUT' => $httpClient->put($endpoint, $data),
                'PATCH' => $httpClient->patch($endpoint, $data),
                'DELETE' => $httpClient->delete($endpoint, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            $duration = (int)((microtime(true) - $startTime) * 1000);
            $responseStatus = $response->status();
            $responseBody = $response->body();

            // Log the API call
            $this->getLoggingService()->log(
                method: $method,
                endpoint: $endpoint,
                requestHeaders: $sanitizedHeaders,
                requestBody: $requestBody,
                responseStatus: $responseStatus,
                responseBody: $responseBody,
                durationMs: $duration,
                operation: $operation,
                errorMessage: null
            );

            return $response;
        } catch (\Exception $e) {
            $duration = (int)((microtime(true) - $startTime) * 1000);
            
            // Log the failed API call
            $this->getLoggingService()->log(
                method: $method,
                endpoint: $endpoint,
                requestHeaders: $sanitizedHeaders,
                requestBody: $requestBody,
                responseStatus: null,
                responseBody: null,
                durationMs: $duration,
                operation: $operation,
                errorMessage: $e->getMessage()
            );

            throw $e;
        }
    }

    /**
     * Make an API call with file attachment and log the request and response
     */
    protected function makeLoggedApiCallWithFile(
        string $method,
        string $endpoint,
        array $headers,
        array $data = [],
        $file = null,
        int $timeout = 30,
        ?string $operation = null
    ): \Illuminate\Http\Client\Response {
        $startTime = microtime(true);
        $sanitizedHeaders = $this->getLoggingService()->sanitizeHeaders($headers);
        $requestBody = !empty($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : null;
        
        // Note: For file uploads, we log the metadata but not the actual file content
        if ($file) {
            $fileName = 'unknown';
            if (method_exists($file, 'getClientOriginalName')) {
                $fileName = $file->getClientOriginalName();
            } elseif (is_string($file)) {
                $fileName = basename($file);
            } elseif (is_object($file) && property_exists($file, 'name')) {
                $fileName = $file->name;
            }
            $requestBody .= ' [FILE ATTACHED: ' . $fileName . ']';
        }

        try {
            $httpClient = Http::withHeaders($headers)->timeout($timeout);
            
            if ($file) {
                // Handle multiple files (use 'files' for multiple, 'file' for single)
                if (is_array($file) && count($file) > 1) {
                    foreach ($file as $f) {
                        $httpClient = $httpClient->attach('files', $f);
                    }
                } else {
                    $fileToAttach = is_array($file) ? $file[0] : $file;
                    $httpClient = $httpClient->attach('file', $fileToAttach);
                }
            }

            $response = match(strtoupper($method)) {
                'POST' => $httpClient->post($endpoint, $data),
                'PUT' => $httpClient->put($endpoint, $data),
                'PATCH' => $httpClient->patch($endpoint, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method for file upload: {$method}"),
            };

            $duration = (int)((microtime(true) - $startTime) * 1000);
            $responseStatus = $response->status();
            $responseBody = $response->body();

            // Log the API call
            $this->getLoggingService()->log(
                method: $method,
                endpoint: $endpoint,
                requestHeaders: $sanitizedHeaders,
                requestBody: $requestBody,
                responseStatus: $responseStatus,
                responseBody: $responseBody,
                durationMs: $duration,
                operation: $operation,
                errorMessage: null
            );

            return $response;
        } catch (\Exception $e) {
            $duration = (int)((microtime(true) - $startTime) * 1000);
            
            // Log the failed API call
            $this->getLoggingService()->log(
                method: $method,
                endpoint: $endpoint,
                requestHeaders: $sanitizedHeaders,
                requestBody: $requestBody,
                responseStatus: null,
                responseBody: null,
                durationMs: $duration,
                operation: $operation,
                errorMessage: $e->getMessage()
            );

            throw $e;
        }
    }
}

