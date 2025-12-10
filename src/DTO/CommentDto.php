<?php

namespace Davoodf1995\Desk365\DTO;

/**
 * DTO for adding replies to tickets
 * Matches AddReplyModel from API spec
 */
class ReplyDto
{
    use DTOCommon;

    public function __construct(
        public string $body,
        public ?string $cc_emails = null, // Comma-separated emails
        public ?string $bcc_emails = null, // Comma-separated emails
        public ?string $agent_email = null,
        public ?string $from_email = null,
        public ?int $include_prev_ccs = 0, // 0 or 1
        public ?int $include_prev_messages = 0, // 0 or 1
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'body' => $this->body,
            'cc_emails' => $this->cc_emails,
            'bcc_emails' => $this->bcc_emails,
            'agent_email' => $this->agent_email,
            'from_email' => $this->from_email,
            'include_prev_ccs' => $this->include_prev_ccs,
            'include_prev_messages' => $this->include_prev_messages,
        ], fn($value) => $value !== null);
    }
}

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
}

/**
 * @deprecated Use ReplyDto or NoteDto instead
 */
class CommentDto extends ReplyDto
{
}



