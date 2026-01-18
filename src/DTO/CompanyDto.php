<?php

namespace Devmatika\Desk365\DTO;

/**
 * DTO for creating and updating companies
 * Matches CompanyRequestModel from API spec
 */
class CompanyDto
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $phone = null,
        public ?string $address = null,
        public ?string $city = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $zipcode = null,
        public ?string $sla = null,
        public ?string $domains = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'zipcode' => $this->zipcode,
            'sla' => $this->sla,
            'domains' => $this->domains,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            country: $data['country'] ?? null,
            zipcode: $data['zipcode'] ?? null,
            sla: $data['sla'] ?? null,
            domains: $data['domains'] ?? null,
        );
    }
}

