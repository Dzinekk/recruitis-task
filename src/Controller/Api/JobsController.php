<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Dto\JobReplyDto;
use App\Exception\ApiException;
use App\Exception\SerializeException;
use App\Service\JobsApiClient;
use Closure;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/api/jobs', name: 'api_jobs')]
class JobsController extends AbstractController {
    private const int DEFAULT_CACHE_TTL = 600; // 10 minutes
    private const int DEFAULT_LIMIT = 10;

    public function __construct(
        private readonly JobsApiClient $apiClient,
        private readonly CacheInterface $cache,
    ) {}

    /** Get a paginated list of jobs
     * @param Request $request
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', self::DEFAULT_LIMIT);

        //cache by page number only
        $cacheKey = sprintf('jobs_list_page_%d', $page);
        $apiCall = fn() => $this->apiClient->fetchJobs($page, $limit);

        return $this->handleApiCall($apiCall, $cacheKey);
    }

    /** Get a single job by ID
     * @param int $jobId
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/{jobId}', name: 'detail', requirements: ['jobId' => '\d+'], methods: ['GET'])]
    public function detail(int $jobId): JsonResponse {
        //cache by job ID
        $cacheKey = sprintf('job_detail_%d', $jobId);
        $apiCall = fn() => $this->apiClient->fetchJobById($jobId);

        return $this->handleApiCall($apiCall, $cacheKey);
    }

    /** Reply to a job offer
     * @param int $jobId
     * @param JobReplyDto $replyDto
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    #[Route('/{jobId}/reply', name: 'reply', requirements: ['jobId' => '\d+'], methods: ['POST'])]
    public function reply(int $jobId, #[MapRequestPayload] JobReplyDto $replyDto): JsonResponse {
        $apiCall = fn() => $this->apiClient->submitReply($jobId, $replyDto);

        return $this->handleApiCall($apiCall);
    }

    /** Handle API calls with caching and error handling
     *
     * @param Closure $apiCall
     * @param string|null $cacheKey key to be used for cache, if null no caching is applied
     * @return JsonResponse
     * @throws InvalidArgumentException
     */
    private function handleApiCall(Closure $apiCall, ?string $cacheKey = null): JsonResponse {
        try {
            // If no cache key is provided, execute the API call directly
            if ($cacheKey === null) {
                $data = $apiCall();
                return $this->json($data);
            }

            //cache only successful API responses
            $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($apiCall) {
                $item->expiresAfter(self::DEFAULT_CACHE_TTL);
                return $apiCall();
            });

            return $this->json($data);
        } catch (ApiException $e) {
            return $this->json(
                ['message' => "Failed to fetch jobs from external API. Error: {$e->getMessage()}"],
                Response::HTTP_SERVICE_UNAVAILABLE
            );
        } catch (SerializeException $e) {
            return $this->json(
                ['message' => "Failed to deserialize data from API. Error: {$e->getMessage()}"],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
