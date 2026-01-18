<?php

namespace Devmatika\Desk365\Traits;

use Devmatika\Desk365\Services\ApiLoggingService;
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
     * Get file content, filename, and MIME type from various file input types
     * 
     * @param mixed $file File input (UploadedFile object, file path string, or file content)
     * @return array [fileContent, fileName, contentType]
     */
    protected function getFileInfo($file): array
    {
        $fileContent = null;
        $fileName = 'unknown';
        $contentType = 'application/octet-stream';

        // Handle UploadedFile objects
        if (is_object($file) && method_exists($file, 'getRealPath') && method_exists($file, 'getClientOriginalName')) {
            $filePath = $file->getRealPath();
            $fileName = $file->getClientOriginalName();
            
            // Get file content
            if ($filePath && file_exists($filePath)) {
                $fileContent = file_get_contents($filePath);
            } elseif (method_exists($file, 'getContent')) {
                $fileContent = $file->getContent();
            }
            
            // Get MIME type
            if (method_exists($file, 'getClientMimeType')) {
                $contentType = $file->getClientMimeType() ?: $contentType;
            } elseif (method_exists($file, 'getMimeType')) {
                $contentType = $file->getMimeType() ?: $contentType;
            } elseif ($filePath && file_exists($filePath)) {
                $contentType = mime_content_type($filePath) ?: $contentType;
            }
        }
        // Handle file path strings
        elseif (is_string($file) && file_exists($file)) {
            $fileContent = file_get_contents($file);
            $fileName = basename($file);
            $contentType = mime_content_type($file) ?: $contentType;
        }
        // Handle file content directly (string that's not a file path)
        elseif (is_string($file)) {
            $fileContent = $file;
            $fileName = 'attachment';
            // Try to detect MIME type from content if finfo is available
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $contentType = finfo_buffer($finfo, $fileContent) ?: $contentType;
                finfo_close($finfo);
            }
        }
        // Fallback for other types
        else {
            $fileContent = $file;
            $fileName = is_object($file) && property_exists($file, 'name') ? $file->name : 'attachment';
        }

        return [$fileContent, $fileName, $contentType];
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
                        [$fileContent, $fileName, $contentType] = $this->getFileInfo($f);
                        $httpClient = $httpClient->attach('files', $fileContent, $fileName, ['Content-Type' => $contentType]);
                    }
                } else {
                    // Single file: use 'file' parameter
                    $fileToAttach = is_array($file) ? $file[0] : $file;
                    [$fileContent, $fileName, $contentType] = $this->getFileInfo($fileToAttach);
                    $httpClient = $httpClient->attach('file', $fileContent, $fileName, ['Content-Type' => $contentType]);
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

