<?php

namespace Davoodf1995\Desk365\DTO;

class TicketCreateDto
{
    use DTOCommon;
    
    public function __construct(
        public string $email,
        public string $subject,
        public string $description,
        public string $assignedTo,
        public string $group,
        public string $category,
        public ?string $subcategory = null,
        public array $customFields = [],
        public $file = null,
        public string $status = "open",
        public int $priority = 1,
        public string $type = "Question",
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'email' => $this->email,
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



