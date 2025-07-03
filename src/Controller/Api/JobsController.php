<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\JobsApiClient;
use Psr\Cache\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/api/jobs', name: 'api_jobs')]
class JobsController extends AbstractController {
    private const int DEFAULT_CACHE_TTL = 3600; // 1 hour
    private const int DEFAULT_LIMIT = 10;
    private const string API_TOKEN = '89d985c4b1c25c26fe3b1595b4ef3137a0ebb549.11169.dd37716503850db285a143eeef3dd663';

    public function __construct(
        private readonly JobsApiClient $apiClient,
        private readonly CacheInterface $cache,
    ) {
    }

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

        $data = $this->cache->get($cacheKey, function (ItemInterface $item) use ($page, $limit) {
            $item->expiresAfter(self::DEFAULT_CACHE_TTL);

            try {
                return $this->apiClient->fetchJobs(self::API_TOKEN, $page, $limit);
            } catch (\Exception $e) {
                return [
                    'error' => "Failed to fetch jobs from external API. Error: {$e->getMessage()}",
                    'code' => 500,
                ];
            }
        });

        return $this->json($data);
    }
}
