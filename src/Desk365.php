<?php

namespace Davoodf1995\Desk365;

use Davoodf1995\Desk365\Services\Desk365TicketingService;
use Davoodf1995\Desk365\DTO\ApiConfigDto;
use Davoodf1995\Desk365\Http\Controllers\{
    TicketController,
    AgentController,
    CustomerController,
    CommentController,
    AttachmentController,
    ReportController
};
use Davoodf1995\Desk365\DTO\{
    TicketCreateDto,
    TicketUpdateDto,
    TicketFilterDto,
    CommentDto,
    AgentDto,
    CustomerDto
};

class Desk365
{
    private static ?ApiConfigDto $config = null;
    private static ?TicketController $ticketController = null;
    private static ?AgentController $agentController = null;
    private static ?CustomerController $customerController = null;
    private static ?CommentController $commentController = null;
    private static ?AttachmentController $attachmentController = null;
    private static ?ReportController $reportController = null;
    private static ?Desk365TicketingService $service = null;

    /**
     * Initialize the Desk365 service with configuration
     */
    public static function init(ApiConfigDto $config): void
    {
        self::$config = $config;
        self::$ticketController = new TicketController($config);
        self::$agentController = new AgentController($config);
        self::$customerController = new CustomerController($config);
        self::$commentController = new CommentController($config);
        self::$attachmentController = new AttachmentController($config);
        self::$reportController = new ReportController($config);
        self::$service = new Desk365TicketingService($config);
    }

    /**
     * Get configuration
     */
    private static function getConfig(): ApiConfigDto
    {
        if (self::$config === null) {
            self::$config = ApiConfigDto::fromArray(config('desk365', []));
            self::init(self::$config);
        }
        return self::$config;
    }

    // ========== TICKET OPERATIONS ==========

    public static function getAllTickets(?TicketFilterDto $filters = null)
    {
        self::getConfig();
        return self::$ticketController->getAll($filters);
    }

    public static function getTicket(string $ticketId)
    {
        self::getConfig();
        return self::$ticketController->getById($ticketId);
    }

    public static function createTicket(TicketCreateDto $ticketData)
    {
        self::getConfig();
        return self::$ticketController->create($ticketData);
    }

    public static function updateTicket(string $ticketId, TicketUpdateDto $ticketData)
    {
        self::getConfig();
        return self::$ticketController->update($ticketId, $ticketData);
    }

    public static function deleteTicket(string $ticketId)
    {
        self::getConfig();
        return self::$ticketController->delete($ticketId);
    }

    public static function searchTickets(string $query, ?TicketFilterDto $filters = null)
    {
        self::getConfig();
        return self::$ticketController->search($query, $filters);
    }

    public static function closeTicket(string $ticketId)
    {
        self::getConfig();
        $updateData = new TicketUpdateDto(status: 'closed');
        return self::$ticketController->update($ticketId, $updateData);
    }

    public static function reopenTicket(string $ticketId)
    {
        self::getConfig();
        $updateData = new TicketUpdateDto(status: 'open');
        return self::$ticketController->update($ticketId, $updateData);
    }

    public static function updateTicketStatus(string $ticketId, string $status)
    {
        self::getConfig();
        $updateData = new TicketUpdateDto(status: $status);
        return self::$ticketController->update($ticketId, $updateData);
    }

    public static function updateTicketPriority(string $ticketId, string $priority)
    {
        self::getConfig();
        $updateData = new TicketUpdateDto(priority: (int)$priority);
        return self::$ticketController->update($ticketId, $updateData);
    }

    // ========== COMMENT OPERATIONS ==========

    public static function getComments(string $ticketId, array $params = [])
    {
        self::getConfig();
        return self::$commentController->getAll($ticketId, $params);
    }

    public static function getComment(string $ticketId, string $commentId)
    {
        self::getConfig();
        return self::$commentController->getById($ticketId, $commentId);
    }

    public static function addComment(string $ticketId, CommentDto $comment)
    {
        self::getConfig();
        return self::$commentController->add($ticketId, $comment);
    }

    public static function updateComment(string $ticketId, string $commentId, CommentDto $comment)
    {
        self::getConfig();
        return self::$commentController->update($ticketId, $commentId, $comment);
    }

    public static function deleteComment(string $ticketId, string $commentId)
    {
        self::getConfig();
        return self::$commentController->delete($ticketId, $commentId);
    }

    // ========== ATTACHMENT OPERATIONS ==========

    public static function uploadAttachment(string $ticketId, $file, array $metadata = [])
    {
        self::getConfig();
        return self::$attachmentController->upload($ticketId, $file, $metadata);
    }

    public static function getAttachments(string $ticketId, array $params = [])
    {
        self::getConfig();
        return self::$attachmentController->getAll($ticketId, $params);
    }

    public static function getAttachment(string $ticketId, string $attachmentId)
    {
        self::getConfig();
        return self::$attachmentController->getById($ticketId, $attachmentId);
    }

    public static function deleteAttachment(string $ticketId, string $attachmentId)
    {
        self::getConfig();
        return self::$attachmentController->delete($ticketId, $attachmentId);
    }

    public static function downloadAttachment(string $ticketId, string $attachmentId)
    {
        self::getConfig();
        return self::$attachmentController->download($ticketId, $attachmentId);
    }

    // ========== AGENT OPERATIONS ==========

    public static function getAllAgents(array $params = [])
    {
        self::getConfig();
        return self::$agentController->getAll($params);
    }

    public static function getAgent(string $agentId)
    {
        self::getConfig();
        return self::$agentController->getById($agentId);
    }

    public static function createAgent(AgentDto $agentData)
    {
        self::getConfig();
        return self::$agentController->create($agentData);
    }

    public static function updateAgent(string $agentId, AgentDto $agentData)
    {
        self::getConfig();
        return self::$agentController->update($agentId, $agentData);
    }

    public static function deleteAgent(string $agentId)
    {
        self::getConfig();
        return self::$agentController->delete($agentId);
    }

    // ========== CUSTOMER OPERATIONS ==========

    public static function getAllCustomers(array $params = [])
    {
        self::getConfig();
        return self::$customerController->getAll($params);
    }

    public static function getCustomer(string $customerId)
    {
        self::getConfig();
        return self::$customerController->getById($customerId);
    }

    public static function createCustomer(CustomerDto $customerData)
    {
        self::getConfig();
        return self::$customerController->create($customerData);
    }

    public static function updateCustomer(string $customerId, CustomerDto $customerData)
    {
        self::getConfig();
        return self::$customerController->update($customerId, $customerData);
    }

    public static function deleteCustomer(string $customerId)
    {
        self::getConfig();
        return self::$customerController->delete($customerId);
    }

    public static function searchCustomers(string $query, array $params = [])
    {
        self::getConfig();
        return self::$customerController->search($query, $params);
    }

    // ========== REPORT OPERATIONS ==========

    public static function getTicketStatistics(?TicketFilterDto $filters = null)
    {
        self::getConfig();
        return self::$reportController->getTicketStatistics($filters);
    }

    public static function getAgentStatistics(string $agentId, ?string $dateFrom = null, ?string $dateTo = null)
    {
        self::getConfig();
        return self::$reportController->getAgentStatistics($agentId, $dateFrom, $dateTo);
    }

    public static function getDashboardData(array $params = [])
    {
        self::getConfig();
        return self::$reportController->getDashboardData($params);
    }

    // ========== LEGACY METHODS (for backward compatibility) ==========

    /**
     * @deprecated Use createTicket instead
     */
    public static function service(): Desk365TicketingService
    {
        self::getConfig();
        return self::$service;
    }
}
