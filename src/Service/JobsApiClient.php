<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class JobsApiClient {
    private const int DEFAULT_TIMEOUT = 10; // seconds
    private const string BASE_URL = 'https://app.recruitis.io/api2/';

    public function __construct(
        private readonly Client $httpClient,
        private readonly LoggerInterface $logger
    ) {}

    /** Fetch a paginated list of jobs from the external API or cache if found
     * @param string $token API authentication token to be used in Bearer Authorization
     * @param int $page
     * @param int $limit
     * @return array list of jobs
     */
    public function fetchJobs(string $token, int $page, int $limit): array {
        try {
            // API request to fetch jobs
            $response = $this->httpClient->get(self::BASE_URL.'/jobs', [
                'headers' => ['Authorization' => "Bearer $token"],
                'query' => [
                    'page' => $page,
                    'limit' => $limit,
                ],
                'timeout' => self::DEFAULT_TIMEOUT,
            ]);

            // Check if the response is OK
            if ($response->getStatusCode() !== 200) {
                $this->logger->error('Unexpected response status from API', [
                    'status_code' => $response->getStatusCode(),
                    'error' => $response->getReasonPhrase(),
                ]);

                throw new \RuntimeException('Unexpected response status from API', $response->getStatusCode());
            }

            // Decode and return the JSON response into assoc array
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $this->logger->error('Failed to fetch jobs from API', [
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException('API request failed', 0, $e);
        }
    }
}
