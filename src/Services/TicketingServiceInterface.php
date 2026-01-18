<?php

namespace Devmatika\Desk365\Services;

use Devmatika\Desk365\DTO\TicketCreateDto;
use Devmatika\Desk365\DTO\TicketUpdateDto;
use Devmatika\Desk365\DTO\ReplyDto;
use Devmatika\Desk365\DTO\NoteDto;
use Devmatika\Desk365\DTO\ApiResponseDto;

interface TicketingServiceInterface
{
    // Ticket Operations
    public function createTicket(TicketCreateDto $ticketData): ApiResponseDto;
    public function updateTicket(string $ticketNumber, TicketUpdateDto $ticketData): ApiResponseDto;
    public function closeTicket(string $ticketNumber): ApiResponseDto;
    public function reopenTicket(string $ticketNumber): ApiResponseDto;

    // Ticket Replies
    public function addReply(string $ticketNumber, ReplyDto $reply, $files = null): ApiResponseDto;

    // Ticket Notes
    public function addNote(string $ticketNumber, NoteDto $note, $files = null): ApiResponseDto;

    // Ticket Status and Priority
    public function updateTicketStatus(string $ticketNumber, string $status): ApiResponseDto;
    public function updateTicketPriority(string $ticketNumber, int $priority): ApiResponseDto;
}
