<?php

namespace IproSoftwareApi\Tests\Unit;

use GuzzleHttp\Psr7\Uri;
use IproSoftwareApi\AccessToken\NoneCacher;
use IproSoftwareApi\Contracts\AccessTokenCacher;
use IproSoftwareApi\DTOs\ClientCredentials;
use IproSoftwareApi\Exceptions\IproSoftwareApiException;
use IproSoftwareApi\HttpClient;
use IproSoftwareApi\IproSoftwareClient;
use IproSoftwareApi\Tests\TestCase;
use Mockery;

class IproSoftwareClientTest extends TestCase
{
    /** @test */
    public function no_call_with_empty_configuration()
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

    /** @test */
    public function set_http_client()
    {
        $client = new IproSoftwareClient();

        $httpClient = Mockery::mock(HttpClient::class);

        $client->setHttpClient($httpClient);

        $this->assertEquals($httpClient, $client->httpClient());
    }

    /** @test */
    public function set_access_token_cache_manager()
    {
        $cacheManager = Mockery::mock(AccessTokenCacher::class);
        $httpClient   = Mockery::mock(HttpClient::class);

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

    /** @test */
    public function set_access_token_cache_manager_return_self()
    {
        $client = new IproSoftwareClient([
            'requests_path_prefix' => '/api/v1',
        ]);

        $httpClient = new HttpClient(new ClientCredentials(uniqid(), uniqid(), uniqid()), new NoneCacher());

        $client->setHttpClient($httpClient);

        $this->assertEquals($client, $client->setAccessTokenCacheManager(new NoneCacher()));
    }

    /** @test */
    public function creation_default_http_client()
    {
        $apiHost = uniqid();
        $timeout = 30.0;
        $client  = new IproSoftwareClient([
            'api_host'       => $apiHost,
            'client_id'      => uniqid(),
            'client_secret'  => uniqid(),
            'oauth_endpoint' => '/my-oauth',
            'client_conf'    => [
                'timeout'     => $timeout,
                'http_errors' => false,
                'headers'     => [
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
