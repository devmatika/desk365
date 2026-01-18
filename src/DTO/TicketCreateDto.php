<?php

namespace Devmatika\Desk365\DTO;

class TicketCreateDto
{
    use DTOCommon;
    
    public function __construct(
        public string $email,
        public string $subject,
        public string $description,
        public ?string $form_name = null,
        public ?string $assign_to = null,
        public ?string $group = null,
        public ?string $category = null,
        public ?string $sub_category = null,
        public ?array $custom_fields = [],
        public ?array $watchers = [],
        public ?array $share_to = [],
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
            'form_name' => $this->form_name,
            'subject' => $this->subject,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'type' => $this->type,
            'assign_to' => $this->assign_to,
            'group' => $this->group,
            'category' => $this->category,
            'sub_category' => $this->sub_category,
            'custom_fields' => !empty($this->custom_fields) ? $this->custom_fields : null,
            'watchers' => !empty($this->watchers) ? $this->watchers : null,
            'share_to' => !empty($this->share_to) ? $this->share_to : null,
        ], fn($value) => $value !== null && $value !== []);
    }
}



