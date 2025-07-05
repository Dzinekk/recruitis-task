<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\JobDto;
use App\Dto\JobFetchResponseDto;
use App\Dto\JobReplyDto;
use App\Dto\JobsFetchResponseDto;
use App\Exception\ApiException;
use App\Exception\SerializeException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializeExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class JobsApiClient {
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $httpClient,
        private readonly SerializerInterface $serializer,
        // DI configuration from service.yaml
        private readonly string $apiBaseUrl,
        private readonly int $apiTimeout,
        private readonly string $apiToken,
    ) {
    }

    /** Fetch a paginated list of jobs from the external API
     *
     * @param int $page
     * @param int $limit
     * @return JobDto[] list of jobs
     */
    public function fetchJobs(int $page, int $limit): array {
        $response = $this->processRequest(
            'GET',
            "$this->apiBaseUrl/jobs",
            JobsFetchResponseDto::class,
            ['query' => [
                'page' => $page,
                'limit' => $limit,
            ]]
        );

        return $response->payload;
    }

    /**
     * @param int $jobId
     * @return JobDto
     */
    public function fetchJobById(int $jobId): JobDto {
        $response = $this->processRequest(
            'GET',
            "$this->apiBaseUrl/jobs/$jobId",
            JobFetchResponseDto::class,
        );

        return $response->payload;
    }


    public function submitReply(int $jobId, JobReplyDto $replyData): void {
        $query = [
            'job_id' => $jobId,
            'name' => $replyData->name,
            'email' => $replyData->email,
            'phone' => $replyData->phone,
            'message' => $replyData->message,
        ];

        $this->processRequest(
            method: 'POST',
            endpoint: "$this->apiBaseUrl/answers",
            options: [
                'json' => $query,
            ]
        );
    }

    /**
     * @template T of object
     * @param string $method HTTPS method (GET, POST, etc.)
     * @param string $endpoint API endpoint to call
     * @param ?class-string<T> $dtoClass name of the DTO class to deserialize the response into
     * @param array $options
     * @return ?T
     */
    private function processRequest(string $method, string $endpoint, ?string $dtoClass = null, array $options = []): ?object {
        // API request to fetch a single job by ID
        try {
            $response = $this->httpClient->request(
                $method,
                $endpoint,
                [
                    'auth_bearer' => $this->apiToken,
                    'timeout' => $this->apiTimeout,
                ] + $options
            );
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to fetch data from API on transport level. Error: {error}', [
                'error' => $e->getMessage(),
            ]);

            throw new ApiException(
                "Failed to fetch data from API on transport level. Message: {$e->getMessage()}",
                0,
                $e
            );
        }

        try {
            $content = $response->getContent();
        } catch (ExceptionInterface $e) {
            $content = $response->getContent(false);

            $this->logger->error('Failed to fetch data from API {options} {error} {response}', [
                'options' => $options,
                'error' => $e->getMessage(),
                'response' => $content,
            ]);

            throw new ApiException(
                "API request failed. Message: {$e->getMessage()}. Response: {$content}", 0, $e
            );
        }

        if ($dtoClass === null) {
            // If no DTO class is specified, we don't need response data (in case of POST, $response->getContent() will throw exception on error)
            return null;
        }

        try {
            return $this->serializer->deserialize(
                $content,
                $dtoClass,
                'json'
            );
        } catch (SerializeExceptionInterface $e) {
            $this->logger->error('Failed to deserialize data from API {options} {error} {response_body}', [
                'options' => $options,
                'error' => $e->getMessage(),
                'response_body' => $content,
            ]);

            throw new SerializeException('Failed to deserialize data from API', 0, $e);
        }
    }
}
