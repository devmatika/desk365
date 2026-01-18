<?php

namespace Devmatika\Desk365\DTO;

class AgentDto
{
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $role = null,
        public ?bool $isActive = true,
        public ?array $customFields = [],
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_active' => $this->isActive,
            'custom_fields' => $this->customFields,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            role: $data['role'] ?? null,
            isActive: $data['is_active'] ?? $data['isActive'] ?? true,
            customFields: $data['custom_fields'] ?? $data['customFields'] ?? [],
        );
    }
}



