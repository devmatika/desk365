<?php

namespace Devmatika\Desk365;

use Devmatika\Desk365\Services\Desk365TicketingService;
use Devmatika\Desk365\DTO\ApiConfigDto;
use Devmatika\Desk365\Http\Controllers\{
    TicketController,
    AgentController,
    CustomerController,
    CommentController,
    AttachmentController,
    ReportController,
    CompanyController
};
use Devmatika\Desk365\DTO\{
    TicketCreateDto,
    TicketUpdateDto,
    TicketFilterDto,
    ReplyDto,
    NoteDto,
    AgentDto,
    CustomerDto,
    CompanyDto
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
    private static ?CompanyController $companyController = null;
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
        self::$companyController = new CompanyController($config);
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

    public static function getTicket(string $ticketNumber)
    {
        self::getConfig();
        return self::$ticketController->getById($ticketNumber);
    }

    public static function getTicketConversations(string $ticketNumber, array $params = [])
    {
        self::getConfig();
        return self::$ticketController->getConversations($ticketNumber, $params);
    }

    public static function createTicket(TicketCreateDto $ticketData)
    {
        self::getConfig();
        return self::$ticketController->create($ticketData);
    }

    public static function updateTicket(string $ticketNumber, TicketUpdateDto $ticketData)
    {
        self::getConfig();
        return self::$ticketController->update($ticketNumber, $ticketData);
    }

    public static function addReply(string $ticketNumber, ReplyDto $replyData, $files = null)
    {
        self::getConfig();
        return self::$ticketController->addReply($ticketNumber, $replyData, $files);
    }

    public static function addNote(string $ticketNumber, NoteDto $noteData, $files = null)
    {
        self::getConfig();
        return self::$ticketController->addNote($ticketNumber, $noteData, $files);
    }

    public static function closeTicket(string $ticketNumber)
    {
        self::getConfig();
        $updateData = new TicketUpdateDto(status: 'closed');
        return self::$ticketController->update($ticketNumber, $updateData);
    }

    public static function reopenTicket(string $ticketNumber)
    {
        self::getConfig();
        $updateData = new TicketUpdateDto(status: 'open');
        return self::$ticketController->update($ticketNumber, $updateData);
    }

    public static function updateTicketStatus(string $ticketNumber, string $status)
    {
        self::getConfig();
        $updateData = new TicketUpdateDto(status: $status);
        return self::$ticketController->update($ticketNumber, $updateData);
    }

    public static function updateTicketPriority(string $ticketNumber, int $priority)
    {
        self::getConfig();
        $updateData = new TicketUpdateDto(priority: $priority);
        return self::$ticketController->update($ticketNumber, $updateData);
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

    // ========== CONTACT OPERATIONS ==========

    public static function getAllContacts(array $params = [])
    {
        self::getConfig();
        return self::$customerController->getAll($params);
    }

    public static function getContact(string $primaryEmail)
    {
        self::getConfig();
        return self::$customerController->getById($primaryEmail);
    }

    public static function createContact(CustomerDto $contactData)
    {
        self::getConfig();
        return self::$customerController->create($contactData);
    }

    public static function updateContact(string $primaryEmail, CustomerDto $contactData)
    {
        self::getConfig();
        return self::$customerController->update($primaryEmail, $contactData);
    }

    // ========== CUSTOMER OPERATIONS (Legacy - use Contact methods instead) ==========

    /**
     * @deprecated Use getAllContacts() instead
     */
    public static function getAllCustomers(array $params = [])
    {
        return self::getAllContacts($params);
    }

    /**
     * @deprecated Use getContact() instead
     */
    public static function getCustomer(string $primaryEmail)
    {
        return self::getContact($primaryEmail);
    }

    /**
     * @deprecated Use createContact() instead
     */
    public static function createCustomer(CustomerDto $customerData)
    {
        return self::createContact($customerData);
    }

    /**
     * @deprecated Use updateContact() instead
     */
    public static function updateCustomer(string $primaryEmail, CustomerDto $customerData)
    {
        return self::updateContact($primaryEmail, $customerData);
    }

    // ========== COMPANY OPERATIONS ==========

    public static function getAllCompanies(array $params = [])
    {
        self::getConfig();
        return self::$companyController->getAll($params);
    }

    public static function getCompanyByName(string $companyName)
    {
        self::getConfig();
        return self::$companyController->getByName($companyName);
    }

    public static function createCompany(CompanyDto $companyData)
    {
        self::getConfig();
        return self::$companyController->create($companyData);
    }

    public static function updateCompany(string $companyName, CompanyDto $companyData)
    {
        self::getConfig();
        return self::$companyController->update($companyName, $companyData);
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
