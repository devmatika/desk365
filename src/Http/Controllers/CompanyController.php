<?php

namespace Devmatika\Desk365\Http\Controllers;

use Devmatika\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    CompanyDto
};
use Devmatika\Desk365\Traits\LogsApiCalls;
use Devmatika\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class CompanyController
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
            $endpoint = $this->getEndpoint('companies');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getAllCompanies'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get All Companies', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get companies: ' . $e->getMessage());
        }
    }

    public function getByName(string $companyName): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('companies/details');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: ['name' => $companyName],
                timeout: $this->config->timeout,
                operation: 'getCompanyByName'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Company', ['company_name' => $companyName, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get company: ' . $e->getMessage());
        }
    }

    public function create(CompanyDto $companyData): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('companies/create');
            $response = $this->makeLoggedApiCall(
                method: 'POST',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $companyData->toArray(),
                timeout: $this->config->timeout,
                operation: 'createCompany'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create Company', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to create company: ' . $e->getMessage());
        }
    }

    public function update(string $companyName, CompanyDto $companyData): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('companies/update', ['company_name' => $companyName]);
            $response = $this->makeLoggedApiCall(
                method: 'PUT',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $companyData->toArray(),
                timeout: $this->config->timeout,
                operation: 'updateCompany'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update Company', ['company_name' => $companyName, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to update company: ' . $e->getMessage());
        }
    }
}

