<?php

namespace Davoodf1995\Desk365\Services;

use Davoodf1995\Desk365\DTO\TicketCreateDto;
use Davoodf1995\Desk365\DTO\TicketUpdateDto;
use Davoodf1995\Desk365\DTO\CommentDto;
use Davoodf1995\Desk365\DTO\ApiResponseDto;

interface TicketingServiceInterface
{
    // Ticket Operations
    public function createTicket(TicketCreateDto $ticketData): ApiResponseDto;
    public function updateTicket(string $ticketId, TicketUpdateDto $ticketData): ApiResponseDto;
    public function closeTicket(string $ticketId): ApiResponseDto;
    public function reopenTicket(string $ticketId): ApiResponseDto;

    // Ticket Comments/Messages
    public function addComment(string $ticketId, CommentDto $comment): ApiResponseDto;

    // Ticket Status and Priority
    public function updateTicketStatus(string $ticketId, string $status): ApiResponseDto;
    public function updateTicketPriority(string $ticketId, string $priority): ApiResponseDto;
}

