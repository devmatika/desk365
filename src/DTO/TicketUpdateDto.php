<?php

namespace Davoodf1995\Desk365\DTO;

class TicketUpdateDto
{
    public function __construct(
        public ?string $subject = null,
        public ?string $description = null,
        public ?string $status = "open",
        public ?int $priority = 1,
        public ?string $type = "Question",
        public ?string $assignedTo = null,
        public ?string $group = null,
        public ?string $category = null,
        public ?string $subcategory = null,
        public ?array $customFields = [],
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'type' => $this->type,
            'assignedTo' => $this->assignedTo,
            'group' => $this->group,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'customFields' => $this->customFields,
        ], fn($value) => $value !== null);
    }
}



