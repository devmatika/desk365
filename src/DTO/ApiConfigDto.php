<?php

namespace Davoodf1995\Desk365\DTO;

class ApiConfigDto
{
    public function __construct(
        public string $baseUrl,
        public string $apiKey,
        public ?string $apiSecret = null,
        public ?int $timeout = 30,
        public ?int $retryAttempts = 3,
        public ?array $headers = null,
        public ?string $version = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            baseUrl: $data['base_url'] ?? '',
            apiKey: $data['api_key'] ?? '',
            apiSecret: $data['api_secret'] ?? null,
            timeout: $data['timeout'] ?? 30,
            retryAttempts: $data['retry_attempts'] ?? 3,
            headers: $data['headers'] ?? null,
            version: $data['version'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'base_url' => $this->baseUrl,
            'api_key' => $this->apiKey,
            'api_secret' => $this->apiSecret,
            'timeout' => $this->timeout,
            'retry_attempts' => $this->retryAttempts,
            'headers' => $this->headers,
            'version' => $this->version,
        ], fn($value) => $value !== null);
    }

    public function getAuthHeaders(): array
    {
        $headers = $this->headers ?? [];
        $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        
        if ($this->apiSecret) {
            $headers['X-API-Secret'] = $this->apiSecret;
        }
        
        return $headers;
    }
}



