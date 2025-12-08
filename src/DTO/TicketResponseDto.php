<?php

namespace Davoodf1995\Desk365\DTO;

class TicketResponseDto
{
    public function __construct(
        public ?string $id = null,
        public ?string $ticketNumber = null,
        public ?string $subject = null,
        public ?string $description = null,
        public ?string $status = null,
        public ?int $priority = null,
        public ?string $type = null,
        public ?string $assignedTo = null,
        public ?string $group = null,
        public ?string $category = null,
        public ?string $subcategory = null,
        public ?string $customerId = null,
        public ?string $customerEmail = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?array $attachments = [],
        public ?array $comments = [],
        public ?array $customFields = [],
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'ticket_number' => $this->ticketNumber,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'type' => $this->type,
            'assigned_to' => $this->assignedTo,
            'group' => $this->group,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'customer_id' => $this->customerId,
            'customer_email' => $this->customerEmail,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'attachments' => $this->attachments,
            'comments' => $this->comments,
            'custom_fields' => $this->customFields,
        ], fn($value) => $value !== null);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            ticketNumber: $data['ticket_number'] ?? $data['ticket_number'] ?? null,
            subject: $data['subject'] ?? null,
            description: $data['description'] ?? null,
            status: $data['status'] ?? null,
            priority: $data['priority'] ?? null,
            type: $data['type'] ?? null,
            assignedTo: $data['assigned_to'] ?? $data['assignedTo'] ?? null,
            group: $data['group'] ?? null,
            category: $data['category'] ?? null,
            subcategory: $data['subcategory'] ?? null,
            customerId: $data['customer_id'] ?? $data['customerId'] ?? null,
            customerEmail: $data['customer_email'] ?? $data['customerEmail'] ?? null,
            createdAt: $data['created_at'] ?? $data['createdAt'] ?? null,
            updatedAt: $data['updated_at'] ?? $data['updatedAt'] ?? null,
            attachments: $data['attachments'] ?? [],
            comments: $data['comments'] ?? [],
            customFields: $data['custom_fields'] ?? $data['customFields'] ?? [],
        );
    }
}



