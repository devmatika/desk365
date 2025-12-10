<?php

namespace Davoodf1995\Desk365\DTO;

class TicketFilterDto
{
    public function __construct(
        public ?string $ticket_count = null, // "30", "50", or "100"
        public ?string $offset = null,
        public ?string $include_description = null, // "0" or "1"
        public ?string $include_custom_fields = null, // "0" or "1"
        public ?string $include_survey_details = null, // "0" or "1"
        public ?string $nested_fields = null, // "0" or "1"
        public ?string $order_by = null, // "created_time" or "updated_time"
        public ?string $order_type = null, // "asc" or "desc"
        public ?string $updated_since = null, // "yyyy-mm-dd hh:mm:ss"
        public ?string $filters = null, // JSON string with filter object
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'ticket_count' => $this->ticket_count,
            'offset' => $this->offset,
            'include_description' => $this->include_description,
            'include_custom_fields' => $this->include_custom_fields,
            'include_survey_details' => $this->include_survey_details,
            'nested_fields' => $this->nested_fields,
            'order_by' => $this->order_by,
            'order_type' => $this->order_type,
            'updated_since' => $this->updated_since,
            'filters' => $this->filters,
        ], fn($value) => $value !== null);
    }

    /**
     * Helper method to build filters JSON string
     * @param array $filterData Array with keys: status, priority, type, group, assigned_to, category, subcategory, source, contact
     * @return string JSON encoded filters
     */
    public static function buildFilters(array $filterData): string
    {
        return json_encode($filterData);
    }
}



