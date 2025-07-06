<?php

namespace App\Tests;

use App\Dto\JobDto;
use App\Dto\JobReplyDto;
use App\Exception\ApiException;
use App\Exception\SerializeException;
use App\Service\JobsApiClient;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Serializer\SerializerInterface;

class JobsApiClientTest extends KernelTestCase {
    private SerializerInterface $serializer;

    protected function setUp(): void {
        self::bootKernel();
        $this->serializer = static::getContainer()
            ->get(SerializerInterface::class);
    }

    /**
     * Test for fetchJobs method in JobsApiClient.
     *
     * @dataProvider jobsResponseProvider
     */
    #[DataProvider('jobsResponseProvider')]
    public function testFetchJobs(string $responseBody, string $expectedExceptionClass, array $expectedResult): void {
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
        }

        [$apiClient] = $this->getClients($responseBody);

        $result = $apiClient->fetchJobs(1, 10);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test for fetchJobById method in JobsApiClient.
     *
     * @dataProvider jobByIdResponseProvider
     */
    #[DataProvider('jobByIdResponseProvider')]
    public function testFetchJobById(string $responseBody, string $expectedExceptionClass, ?JobDto $expectedResult, int $expectedCode = 200): void {
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
        }

        [$apiClient] = $this->getClients($responseBody, $expectedCode);

        $result = $apiClient->fetchJobById(1);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Test for submitReply method in JobsApiClient.
     *
     * @dataProvider submitReplyProvider
     */
    #[DataProvider('submitReplyProvider')]
    public function testSubmitReply(string $expectedExceptionClass, int $expectedCode = 201): void {
        if ($expectedExceptionClass) {
            $this->expectException($expectedExceptionClass);
        }

        [$apiClient, $mockHttpClient] = $this->getClients('', $expectedCode);

        $replyDto = new JobReplyDto(
            name: 'Test Tester',
            email: 'test@example.com',
            phone: '123456789',
            message: 'I am interested.'
        );

        $apiClient->submitReply(1, $replyDto);

        // reply does not return data, if no exception occurred, it means it was successful, so we can check the request count
        $this->assertSame(1, $mockHttpClient->getRequestsCount());
    }

    /**
     * Data provider for testFetchJobs.
     */
    public static function jobsResponseProvider(): \Generator {
        // success, one job
        yield 'successful response with one job' => [
            'responseBody' => json_encode([
                'payload' => [['job_id' => 123, 'title' => 'Test Job', 'description' => 'Test job description']],
                'meta' => ['code' => 'api.found', 'duration' => 10, 'message' => 'ok'],
            ]),
            'expectedExceptionClass' => '',
            'expectedResult' => [
                new JobDto(
                    job_id: 123,
                    title: 'Test Job',
                    description: 'Test job description',
                ),
            ],
        ];

        // serialization error
        yield 'response with serialization error' => [
            'responseBody' => json_encode([
                'payload' => [['job_id' => 456]],
                'meta' => ['code' => 'api.found', 'duration' => 10, 'message' => 'ok'],
            ]),
            'expectedExceptionClass' => SerializeException::class,
            'expectedResult' => [],
        ];

        // success, no jobs returned
        yield 'successful empty response' => [
            'responseBody' => json_encode([
                'payload' => [],
                'meta' => ['code' => 'api.found', 'duration' => 10, 'message' => 'ok'],
            ]),
            'expectedExceptionClass' => '',
            'expectedResult' => [],
        ];
    }

    /**
     * Data provider for testFetchJobById.
     */
    public static function jobByIdResponseProvider(): \Generator {
        // success, the job was found
        yield 'successful response' => [
            'responseBody' => json_encode([
                'payload' => ['job_id' => 1, 'title' => 'Test Job', 'description' => 'Test job description'],
                'meta' => ['code' => 'api.found', 'duration' => 10, 'message' => 'ok'],
            ]),
            'expectedExceptionClass' => '',
            'expectedResult' =>
                new JobDto(
                    job_id: 1,
                    title: 'Test Job',
                    description: 'Test job description',
                ),
        ];

        // serialization error
        yield 'response with serialization error' => [
            'responseBody' => json_encode([
                'payload' => ['job_id' => 1],
                'meta' => ['code' => 'api.found', 'duration' => 10, 'message' => 'ok'],
            ]),
            'expectedExceptionClass' => SerializeException::class,
            'expectedResult' => null,
        ];

        // job not found
        yield 'error not found' => [
            'responseBody' => json_encode([
                'payload' => [],
                'meta' => ['code' => 'api.found', 'duration' => 10, 'message' => 'ok'],
            ]),
            'expectedExceptionClass' => ApiException::class,
            'expectedResult' => null,
            'expectedCode' => 404,
        ];
    }

    /**
     * Data provider pro testSubmitReply.
     */
    public static function submitReplyProvider(): \Generator {
        yield 'successful submission' => [
            'expectedExceptionClass' => '',
        ];

        yield 'api server error' => [
            'expectedExceptionClass' => ApiException::class,
            'expectedCode' => 500,
        ];
    }

    /** Generate mock http client and JobsApiClient instances
     * @param string $responseBody
     * @param int $expectedCode
     * @return array
     */
    private function getClients(string $responseBody, int $expectedCode = 200): array {
        $mockHttpClient = new MockHttpClient(new MockResponse($responseBody, ['http_code' => $expectedCode]));

        return [
            new JobsApiClient(
                logger: new NullLogger(), // Use NullLogger for testing because it makes a mess in test output
                httpClient: $mockHttpClient,
                serializer: $this->serializer,
                apiBaseUrl: 'http://fake-api.com',
                apiTimeout: 10,
                apiToken: 'fake-token'
            ),
            $mockHttpClient,
        ];
    }
}
