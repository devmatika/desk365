<?php

namespace Davoodf1995\Desk365\DTO;

class CustomerDto
{
    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $email = null,
        public ?string $phone = null,
        public ?string $company = null,
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
            'company' => $this->company,
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
            company: $data['company'] ?? null,
            customFields: $data['custom_fields'] ?? $data['customFields'] ?? [],
        );
    }
}



