<?php

namespace IproSoftwareApi\Tests\Unit;

use BadMethodCallException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use IproSoftwareApi\AccessToken\AccessToken;
use IproSoftwareApi\AccessToken\NoneCacher;
use IproSoftwareApi\Contracts\AccessTokenCacher;
use IproSoftwareApi\DTOs\ClientCredentials;
use IproSoftwareApi\Exceptions\IproSoftwareApiAccessTokenException;
use IproSoftwareApi\Exceptions\IproSoftwareApiException;
use IproSoftwareApi\HttpClient;
use IproSoftwareApi\Tests\TestCase;
use Mockery;
use Psr\Http\Message\ResponseInterface;

class HttpClientTest extends TestCase
{
    protected ClientCredentials $clientCredentials;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientCredentials = new ClientCredentials(uniqid(), uniqid(), uniqid());
    }

    /** @test */
    public function after_init_access_token_is_not_valid()
    {
        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());

        $this->assertFalse($httpClient->hasAccessToken());
    }

    /** @test */
    public function if_access_token_restored_from_cache_then_no_need_api_call()
    {
        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addDay()->toString());

        $this->assertTrue($accessToken->hasAccessToken());

        $cacheManager = Mockery::mock(NoneCacher::class);

        $cacheManager->shouldReceive('get')
            ->once()
            ->andReturn($accessToken);

        $httpClient = new HttpClient($this->clientCredentials, $cacheManager);

        $this->assertEquals($accessToken, $httpClient->generateAccessToken());
    }

    /** @test */
    public function if_access_token_not_in_cache_then_need_api_call()
    {
        $response = Mockery::mock(ResponseInterface::class);
        $client   = Mockery::mock(Client::class);

        $response->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(new Stream(fopen('data://text/plain;base64,' . base64_encode(json_encode([
                    'access_token' => uniqid(),
                    'token_type'   => 'some_type',
                    'expires_in'   => 300,
                ])), 'r')));

        $client->shouldReceive('post')
            ->once()
            ->andReturn($response);

        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());
        $httpClient->setHttp($client);

        $httpClient->generateAccessToken();

        $this->assertTrue($httpClient->hasAccessToken());
    }

    /** @test */
    public function if_access_token_request_return_not_200_status_code_then_throw_exception()
    {
        $response = Mockery::mock(ResponseInterface::class);
        $client   = Mockery::mock(Client::class);

        $response->shouldReceive('getStatusCode')
            ->andReturn(500);

        $client->shouldReceive('post')
            ->once()
            ->andReturn($response);

        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());
        $httpClient->setHttp($client);

        $this->expectException(IproSoftwareApiAccessTokenException::class);

        $httpClient->generateAccessToken();
    }

    /** @test */
    public function mocking_request()
    {
        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addDay()->toString());
        $client      = Mockery::mock(Client::class);
        $httpClient  = new HttpClient($this->clientCredentials, new NoneCacher());
        $this->setProtectedProperty($httpClient, 'accessToken', $accessToken);

        $httpClient->setHttp($client);

        $httpClient->setResponseFilter(function (ResponseInterface $response, $options, $path, $method) {
            $this->assertEquals($path, 'path/to/endpoint');

            return $response;
        });

        $response = new Response(200);

        $options = ['headers' => [
            'Authorization' => $accessToken->getAuthorizationHeader(),
        ]];

        $client->shouldReceive('request')
            ->with('method_name', 'path/to/endpoint', $options)
            ->once()
            ->andReturn($response);

        $clientResponse = $httpClient->request('method_name', 'path/to/endpoint');

        $this->assertEquals($response, $clientResponse);
    }

    /** @test */
    public function mocking_request_if_expired_access_token()
    {
        $accessToken      = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->subMinute()->toString());
        $validAccessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addDay()->toString());
        $client           = Mockery::mock(Client::class);
        $cacheManager     = Mockery::mock(AccessTokenCacher::class);

        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());
        $this->setProtectedProperty($httpClient, 'accessToken', $accessToken);

        $httpClient->setHttp($client);
        $httpClient->setCacheManager($cacheManager);

        $response = new Response(200);

        $client->shouldReceive('request')
            ->once()
            ->andReturn($response);

        $cacheManager->shouldReceive('get')
            ->once()
            ->andReturn($validAccessToken);

        $clientResponse = $httpClient->request('method_name', '/path/to/endpoint');

        $this->assertEquals($response, $clientResponse);
    }

    /** @test */
    public function magic_call()
    {
        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addDay()->toString());
        $client      = Mockery::mock(Client::class);
        $httpClient  = new HttpClient($this->clientCredentials, new NoneCacher());
        $this->setProtectedProperty($httpClient, 'accessToken', $accessToken);

        $httpClient->setHttp($client);
        $response = new Response(200);

        $options = ['headers' => [
            'Authorization' => $accessToken->getAuthorizationHeader(),
        ]];

        foreach (HttpClient::HTTP_METHODS as $METHOD) {
            $client->shouldReceive('request')
                ->with($METHOD, 'path/to/endpoint', $options)
                ->once()
                ->andReturn($response);

            $clientResponse = call_user_func([$httpClient, strtolower($METHOD)], 'path/to/endpoint');

            $this->assertEquals($response, $clientResponse);
        }

        $this->expectException(BadMethodCallException::class);

        $httpClient->someMethod();
    }

    /** @test */
    public function set_cache_manager()
    {
        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());

        $cacheManager = Mockery::mock(AccessTokenCacher::class);

        $this->assertInstanceOf(
            \IproSoftwareApi\Contracts\HttpClient::class,
            $httpClient->setCacheManager($cacheManager)
        );
    }

    /** @test */
    public function get_config()
    {
        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());

        $this->assertNotEmpty($httpClient->getConfig());

        $this->setProtectedProperty($httpClient, 'http', null);

        $this->expectException(IproSoftwareApiException::class);

        $httpClient->getConfig();
    }
}
