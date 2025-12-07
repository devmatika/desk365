<?php

namespace Davoodf1995\Desk365\DTO;

class CommentDto
{
    use DTOCommon;

    public function __construct(
        public string $content,
        public ?string $id = null,
        public ?string $authorId = null,
        public ?string $authorName = null,
        public ?string $authorType = null, // 'agent', 'customer', 'system'
        public ?bool $isPublic = true,
        public ?bool $isInternal = false,
        public ?array $attachments = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'content' => $this->content,
            'author_id' => $this->authorId,
            'author_name' => $this->authorName,
            'author_type' => $this->authorType,
            'is_public' => $this->isPublic,
            'is_internal' => $this->isInternal,
            'attachments' => $this->attachments,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ], fn($value) => $value !== null);
    }
}

