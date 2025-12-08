<?php

namespace Davoodf1995\Desk365\DTO;

class ApiResponseDto
{
    public function __construct(
        public bool $success,
        public mixed $data = null,
        public ?string $message = null,
        public ?array $errors = null,
        public ?int $statusCode = null,
        public ?array $meta = null, // For pagination, rate limits, etc.
    ) {
    }

    public static function success(mixed $data = null, ?string $message = null, ?int $statusCode = 200, ?array $meta = null): self
    {
        return new self(
            success: true,
            data: $data,
            message: $message,
            statusCode: $statusCode,
            meta: $meta,
        );
    }

    public static function error(string $message, ?array $errors = null, ?int $statusCode = 400, ?array $meta = null): self
    {
        return new self(
            success: false,
            message: $message,
            errors: $errors,
            statusCode: $statusCode,
            meta: $meta,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            success: $data['success'] ?? false,
            data: $data['data'] ?? null,
            message: $data['message'] ?? null,
            errors: $data['errors'] ?? null,
            statusCode: $data['status_code'] ?? null,
            meta: $data['meta'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'data' => $this->data,
            'message' => $this->message,
            'errors' => $this->errors,
            'status_code' => $this->statusCode,
            'meta' => $this->meta,
        ], fn($value) => $value !== null);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function isError(): bool
    {
        return !$this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }
}



