<?php

namespace Davoodf1995\Desk365;

use Davoodf1995\Desk365\Services\Desk365TicketingService;
use Davoodf1995\Desk365\DTO\ApiConfigDto;

class Desk365
{
    private static ?Desk365TicketingService $service = null;

    /**
     * Initialize the Desk365 service with configuration
     */
    public static function init(ApiConfigDto $config): Desk365TicketingService
    {
        self::$service = new Desk365TicketingService($config);
        return self::$service;
    }

    /**
     * Get the Desk365 service instance
     */
    public static function service(): Desk365TicketingService
    {
        if (self::$service === null) {
            $config = ApiConfigDto::fromArray(config('desk365', []));
            self::$service = new Desk365TicketingService($config);
        }
        return self::$service;
    }

    // Ticket Operations
    public static function createTicket($ticketData)
    {
        return self::service()->createTicket($ticketData);
    }

    public static function updateTicket(string $ticketId, $ticketData)
    {
        return self::service()->updateTicket($ticketId, $ticketData);
    }

    public static function closeTicket(string $ticketId)
    {
        return self::service()->closeTicket($ticketId);
    }

    public static function reopenTicket(string $ticketId)
    {
        return self::service()->reopenTicket($ticketId);
    }

    // Ticket Comments/Messages
    public static function addComment(string $ticketId, $comment)
    {
        return self::service()->addComment($ticketId, $comment);
    }

    // Ticket Status and Priority
    public static function updateTicketStatus(string $ticketId, string $status)
    {
        return self::service()->updateTicketStatus($ticketId, $status);
    }

    public static function updateTicketPriority(string $ticketId, string $priority)
    {
        return self::service()->updateTicketPriority($ticketId, $priority);
    }
}

