<?php

namespace Davoodf1995\Desk365\DTO;

class TicketFilterDto
{
    public function __construct(
        public ?string $status = null,
        public ?string $priority = null,
        public ?string $assignedTo = null,
        public ?string $group = null,
        public ?string $category = null,
        public ?string $customerId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public ?string $search = null,
        public ?int $page = 1,
        public ?int $perPage = 20,
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'status' => $this->status,
            'priority' => $this->priority,
            'assigned_to' => $this->assignedTo,
            'group' => $this->group,
            'category' => $this->category,
            'customer_id' => $this->customerId,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'search' => $this->search,
            'page' => $this->page,
            'per_page' => $this->perPage,
        ], fn($value) => $value !== null);
    }
}



