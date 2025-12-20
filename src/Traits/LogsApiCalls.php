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
     * 
     * This method ensures compliance with Desk365 API attachment rules:
     * - Content-Type is automatically set to 'multipart/form-data' by Laravel's HTTP client
     * - Multiple files use 'files' parameter (array with count > 1)
     * - Single file uses 'file' parameter
     * - Only files on local machine can be attached (handled by caller)
     * 
     * @param string $method HTTP method (POST, PUT, PATCH)
     * @param string $endpoint API endpoint URL
     * @param array $headers HTTP headers (Content-Type will be removed and set automatically)
     * @param array $data Form data to send
     * @param mixed $file File path(s) - can be string (single file) or array (multiple files)
     * @param int $timeout Request timeout in seconds
     * @param string|null $operation Operation name for logging
     * @return \Illuminate\Http\Client\Response
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
            // Handle array of files
            if (is_array($file)) {
                $fileCount = count($file);
                $firstFile = $file[0] ?? null;
                if ($firstFile) {
                    if (is_object($firstFile) && method_exists($firstFile, 'getClientOriginalName')) {
                        $fileName = $firstFile->getClientOriginalName();
                    } elseif (is_string($firstFile)) {
                        $fileName = basename($firstFile);
                    } elseif (is_object($firstFile) && property_exists($firstFile, 'name')) {
                        $fileName = $firstFile->name;
                    }
                }
                $fileName = $fileCount > 1 ? "{$fileName} (+{$fileCount} files)" : $fileName;
            } elseif (is_object($file) && method_exists($file, 'getClientOriginalName')) {
                $fileName = $file->getClientOriginalName();
            } elseif (is_string($file)) {
                $fileName = basename($file);
            } elseif (is_object($file) && property_exists($file, 'name')) {
                $fileName = $file->name;
            }
            $requestBody .= ' [FILE ATTACHED: ' . $fileName . ']';
        }

        try {
            // Remove Content-Type from headers if present - Laravel's attach() will set it to multipart/form-data automatically
            // This ensures we comply with Desk365 API requirement: "The Content-Type of the request should always be 'multipart/form-data'"
            $headersForFileUpload = $headers;
            unset($headersForFileUpload['Content-Type']);
            unset($headersForFileUpload['content-type']);
            
            $httpClient = Http::withHeaders($headersForFileUpload)->timeout($timeout);
            
            if ($file) {
                // Desk365 API rules:
                // - If you need to attach multiple files, the 'files' parameter should be used instead of 'file'
                // - Single file uses 'file' parameter
                // - Laravel's attach() automatically sets Content-Type to multipart/form-data
                if (is_array($file) && count($file) > 1) {
                    // Multiple files: use 'files' parameter
                    foreach ($file as $f) {
                        // Handle UploadedFile objects by extracting path and filename
                        if (is_object($f) && method_exists($f, 'getRealPath') && method_exists($f, 'getClientOriginalName')) {
                            $httpClient = $httpClient->attach('files', $f->getRealPath(), $f->getClientOriginalName());
                        } else {
                            $httpClient = $httpClient->attach('files', $f);
                        }
                    }
                } else {
                    // Single file: use 'file' parameter
                    $fileToAttach = is_array($file) ? $file[0] : $file;
                    // Handle UploadedFile objects by extracting path and filename
                    if (is_object($fileToAttach) && method_exists($fileToAttach, 'getRealPath') && method_exists($fileToAttach, 'getClientOriginalName')) {
                        $httpClient = $httpClient->attach('file', $fileToAttach->getRealPath(), $fileToAttach->getClientOriginalName());
                    } else {
                        $httpClient = $httpClient->attach('file', $fileToAttach);
                    }
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

