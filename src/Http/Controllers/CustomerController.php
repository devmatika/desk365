<?php

namespace Devmatika\Desk365\Http\Controllers;

use Devmatika\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    CustomerDto
};
use Devmatika\Desk365\Traits\LogsApiCalls;
use Devmatika\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class CustomerController
{
    use LogsApiCalls, HandlesApiResponses;
    private ApiConfigDto $config;
    private string $apiVersion;

    public function __construct(ApiConfigDto $config)
    {
        $this->config = $config;
        $this->apiVersion = $config->version ?? 'v3';
    }

    public function getAll(array $params = []): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('contacts');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getAllContacts'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get All Contacts', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get contacts: ' . $e->getMessage());
        }
    }

    public function getById(string $primaryEmail): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("contacts/details");
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: ['primary_email' => $primaryEmail],
                timeout: $this->config->timeout,
                operation: 'getContact'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Contact', ['primary_email' => $primaryEmail, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get contact: ' . $e->getMessage());
        }
    }

    public function create(CustomerDto $customerData): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('contacts/create');
            $response = $this->makeLoggedApiCall(
                method: 'POST',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $customerData->toArray(),
                timeout: $this->config->timeout,
                operation: 'createContact'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create Contact', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to create contact: ' . $e->getMessage());
        }
    }

    public function update(string $primaryEmail, CustomerDto $customerData): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("contacts/update", ['primary_email' => $primaryEmail]);
            $response = $this->makeLoggedApiCall(
                method: 'PUT',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $customerData->toArray(),
                timeout: $this->config->timeout,
                operation: 'updateContact'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update Contact', ['primary_email' => $primaryEmail, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to update contact: ' . $e->getMessage());
        }
    }

}



