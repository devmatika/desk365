<?php

namespace Devmatika\Desk365\DTO;

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

    /**
     * Create ReplyDto from array, handling array to string conversion for emails
     */
    public static function fromArray(array $data): self
    {
        // Convert arrays to comma-separated strings if needed
        $cc_emails = $data['cc_emails'] ?? null;
        if (is_array($cc_emails)) {
            $cc_emails = implode(',', array_filter($cc_emails));
        }

        $bcc_emails = $data['bcc_emails'] ?? null;
        if (is_array($bcc_emails)) {
            $bcc_emails = implode(',', array_filter($bcc_emails));
        }

        // Convert boolean to integer if needed
        $include_prev_messages = $data['include_prev_messages'] ?? 0;
        if (is_bool($include_prev_messages)) {
            $include_prev_messages = $include_prev_messages ? 1 : 0;
        }

        $include_prev_ccs = $data['include_prev_ccs'] ?? 0;
        if (is_bool($include_prev_ccs)) {
            $include_prev_ccs = $include_prev_ccs ? 1 : 0;
        }

        return new self(
            body: $data['body'],
            cc_emails: $cc_emails ?: null,
            bcc_emails: $bcc_emails ?: null,
            agent_email: $data['agent_email'] ?? null,
            from_email: $data['from_email'] ?? null,
            include_prev_ccs: $include_prev_ccs,
            include_prev_messages: $include_prev_messages
        );
    }
}

