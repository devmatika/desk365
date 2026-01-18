<?php

namespace Devmatika\Desk365\DTO;

/**
 * DTO for adding notes to tickets
 * Matches AddNoteModel from API spec
 */
class NoteDto
{
    use DTOCommon;

    public function __construct(
        public string $body,
        public ?string $agent_email = null,
        public ?string $notify_emails = null, // Comma-separated emails
        public ?int $private_note = 1, // 0 for public, 1 for private
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'body' => $this->body,
            'agent_email' => $this->agent_email,
            'notify_emails' => $this->notify_emails,
            'private_note' => $this->private_note,
        ], fn($value) => $value !== null);
    }

    /**
     * Create NoteDto from array, handling array to string conversion for emails
     */
    public static function fromArray(array $data): self
    {
        // Convert arrays to comma-separated strings if needed
        $notify_emails = $data['notify_emails'] ?? null;
        if (is_array($notify_emails)) {
            $notify_emails = implode(',', array_filter($notify_emails));
        }

        // Convert boolean to integer if needed
        $private_note = $data['private_note'] ?? 1;
        if (is_bool($private_note)) {
            $private_note = $private_note ? 1 : 0;
        }

        return new self(
            body: $data['body'],
            agent_email: $data['agent_email'] ?? null,
            notify_emails: $notify_emails ?: null,
            private_note: $private_note
        );
    }
}

