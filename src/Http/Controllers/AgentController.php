<?php

namespace Devmatika\Desk365\Http\Controllers;

use Devmatika\Desk365\DTO\{
    ApiResponseDto,
    ApiConfigDto,
    AgentDto
};
use Devmatika\Desk365\Traits\LogsApiCalls;
use Devmatika\Desk365\Traits\HandlesApiResponses;
use Illuminate\Support\Facades\Log;

class AgentController
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
            $endpoint = $this->getEndpoint('agents');
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $params,
                timeout: $this->config->timeout,
                operation: 'getAllAgents'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get All Agents', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get agents: ' . $e->getMessage());
        }
    }

    public function getById(string $agentId): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("agents/{$agentId}");
            $response = $this->makeLoggedApiCall(
                method: 'GET',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [],
                timeout: $this->config->timeout,
                operation: 'getAgent'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Get Agent', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to get agent: ' . $e->getMessage());
        }
    }

    public function create(AgentDto $agentData): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint('agents');
            $response = $this->makeLoggedApiCall(
                method: 'POST',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $agentData->toArray(),
                timeout: $this->config->timeout,
                operation: 'createAgent'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Create Agent', ['error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to create agent: ' . $e->getMessage());
        }
    }

    public function update(string $agentId, AgentDto $agentData): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("agents/{$agentId}");
            $response = $this->makeLoggedApiCall(
                method: 'PUT',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: $agentData->toArray(),
                timeout: $this->config->timeout,
                operation: 'updateAgent'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Update Agent', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to update agent: ' . $e->getMessage());
        }
    }

    public function delete(string $agentId): ApiResponseDto
    {
        try {
            $endpoint = $this->getEndpoint("agents/{$agentId}");
            $response = $this->makeLoggedApiCall(
                method: 'DELETE',
                endpoint: $endpoint,
                headers: $this->config->getAuthHeaders(),
                data: [],
                timeout: $this->config->timeout,
                operation: 'deleteAgent'
            );

            return $this->handleResponse($response);
        } catch (\Exception $e) {
            Log::error('Desk365 API Error - Delete Agent', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
            return ApiResponseDto::error('Failed to delete agent: ' . $e->getMessage());
        }
    }

}



