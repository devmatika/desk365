<?php

namespace Davoodf1995\Desk365\DTO;

class AttachmentDto
{
    public function __construct(
        public ?string $id = null,
        public ?string $fileName = null,
        public ?string $fileSize = null,
        public ?string $fileType = null,
        public ?string $fileUrl = null,
        public ?string $uploadedAt = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'file_name' => $this->fileName,
            'file_size' => $this->fileSize,
            'file_type' => $this->fileType,
            'file_url' => $this->fileUrl,
            'uploaded_at' => $this->uploadedAt,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            fileName: $data['file_name'] ?? $data['fileName'] ?? null,
            fileSize: $data['file_size'] ?? $data['fileSize'] ?? null,
            fileType: $data['file_type'] ?? $data['fileType'] ?? null,
            fileUrl: $data['file_url'] ?? $data['fileUrl'] ?? null,
            uploadedAt: $data['uploaded_at'] ?? $data['uploadedAt'] ?? null,
        );
    }
}



