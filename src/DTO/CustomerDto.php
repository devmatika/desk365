<?php

namespace Devmatika\Desk365\DTO;

/**
 * DTO for creating and updating contacts
 * Matches CreateContactRequestModel and UpdateContactRequestModel from API spec
 */
class CustomerDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $primary_email = null,
        public ?string $secondary_emails = null, // Comma-separated emails
        public ?string $title = null,
        public ?string $mobile = null,
        public ?string $phone = null,
        public ?string $company_name = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'primary_email' => $this->primary_email,
            'secondary_emails' => $this->secondary_emails,
            'title' => $this->title,
            'mobile' => $this->mobile,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            primary_email: $data['primary_email'] ?? $data['email'] ?? null,
            secondary_emails: $data['secondary_emails'] ?? null,
            title: $data['title'] ?? null,
            mobile: $data['mobile'] ?? null,
            phone: $data['phone'] ?? null,
            company_name: $data['company_name'] ?? $data['company'] ?? null,
        );
    }
}



