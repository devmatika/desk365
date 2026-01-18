<?php

namespace Devmatika\Desk365\DTO;

class TicketUpdateDto
{
    public function __construct(
        public ?string $subject = null,
        public ?string $description = null,
        public ?string $sla = null,
        public ?string $status = null,
        public ?int $priority = null,
        public ?string $type = null,
        public ?string $assign_to = null,
        public ?string $group = null,
        public ?string $category = null,
        public ?string $sub_category = null,
        public ?array $custom_fields = null,
        public ?array $watchers = null,
        public ?array $share_to = null,
    ) {
    }

    public function toArray(): array
    {
        $result = array_filter([
            'subject' => $this->subject,
            'description' => $this->description,
            'sla' => $this->sla,
            'status' => $this->status,
            'priority' => $this->priority,
            'type' => $this->type,
            'assign_to' => $this->assign_to,
            'group' => $this->group,
            'category' => $this->category,
            'sub_category' => $this->sub_category,
            'custom_fields' => $this->custom_fields,
        ], fn($value) => $value !== null);

        // Handle watchers and share_to as objects with add/remove arrays
        if ($this->watchers !== null) {
            $result['watchers'] = $this->watchers;
        }
        if ($this->share_to !== null) {
            $result['share_to'] = $this->share_to;
        }

        return $result;
    }
}



