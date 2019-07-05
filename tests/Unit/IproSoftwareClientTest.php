<?php

namespace Angecode\IproSoftware\Tests\Unit;

use Mockery;
use GuzzleHttp\Psr7\Uri;
use Angecode\IproSoftware\HttpClient;
use Angecode\IproSoftware\Tests\TestCase;
use Angecode\IproSoftware\IproSoftwareClient;
use Angecode\IproSoftware\AccessToken\NoneCacher;
use Angecode\IproSoftware\DTOs\ClientCredentials;
use Angecode\IproSoftware\Contracts\AccessTokenCacher;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiException;

class IproSoftwareClientTest extends TestCase
{
    public function testNoCallWithEmptyConfiguration()
    {
        $client = new IproSoftwareClient();

        // no http client created
        $this->assertNull($client->httpClient());

        // default path prefix used
        $this->assertEquals('apis/', $client->getPathPrefix());

        $predefinedMethods = $client->getMethodsList();

        $this->assertIsArray($predefinedMethods);
        $this->assertNotEmpty($predefinedMethods);

        $someMethod = $this->arrayKeyFirst($predefinedMethods);

        $this->expectException(IproSoftwareApiException::class);
        $this->expectExceptionCode(500);

        call_user_func([$client, $someMethod]);
    }

    public function testSetHttpClient()
    {
        $client = new IproSoftwareClient();

        $httpClient = Mockery::mock(HttpClient::class);

        $client->setHttpClient($httpClient);

        $this->assertEquals($httpClient, $client->httpClient());
    }

    public function testSetAccessTokenCacheManager()
    {
        $cacheManager = Mockery::mock(AccessTokenCacher::class);
        $httpClient = Mockery::mock(HttpClient::class);

        $client = new IproSoftwareClient();

        $this->expectException(IproSoftwareApiException::class);
        $this->expectExceptionCode(500);

        $client->setAccessTokenCacheManager($cacheManager);

        $client->setHttpClient($httpClient);

        $httpClient->shouldReceive('setCacheManager')
            ->with($cacheManager)
            ->once();

        $client->setAccessTokenCacheManager($cacheManager);
    }

    public function testSetAccessTokenCacheManagerReturnSelf()
    {
        $client = new IproSoftwareClient([
            'requests_path_prefix' => '/api/v1',
        ]);

        $httpClient = new HttpClient(new ClientCredentials(uniqid(), uniqid(), uniqid()), new NoneCacher());

        $client->setHttpClient($httpClient);

        $this->assertEquals($client, $client->setAccessTokenCacheManager(new NoneCacher()));
    }

    public function testCreationDefaultHttpClient()
    {
        $apiHost = uniqid();
        $timeout = 30.0;
        $client = new IproSoftwareClient([
            'api_host' => $apiHost,
            'client_id' => uniqid(),
            'client_secret' => uniqid(),
            'oauth_endpoint' => '/my-oauth',
            'client_conf' => [
                'timeout' => $timeout,
                'http_errors' => false,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ],
        ]);

        $this->assertNotNull($client->httpClient());

        /** @var Uri $baseUri */
        $baseUri = $client->httpClient()->getConfig('base_uri');
        $this->assertEquals($apiHost, $baseUri->getPath());

        $this->assertEquals('30', $client->httpClient()->getConfig('timeout'));
    }
}
