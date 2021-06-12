<?php

namespace Angecode\IproSoftware\Tests\Unit;

use Angecode\IproSoftware\AccessToken\AccessToken;
use Angecode\IproSoftware\AccessToken\NoneCacher;
use Angecode\IproSoftware\Contracts\AccessTokenCacher;
use Angecode\IproSoftware\DTOs\ClientCredentials;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiAccessTokenException;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiException;
use Angecode\IproSoftware\HttpClient;
use Angecode\IproSoftware\Tests\TestCase;
use BadMethodCallException;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Psr\Http\Message\ResponseInterface;

class HttpClientTest extends TestCase
{
    protected $clientCredentials;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clientCredentials = new ClientCredentials(uniqid(), uniqid(), uniqid());
    }

    public function testAfterInitAccessTokenIsNotValid()
    {
        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());

        $this->assertFalse($httpClient->hasAccessToken());
    }

    public function testIfAccessTokenGetFromCacheThenNoNeedApiCall()
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

    public function testIfAccessTokenNotInCacheThenNeedApiCall()
    {
        $response = Mockery::mock(ResponseInterface::class);
        $client   = Mockery::mock(Client::class);

        $response->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode([
                'access_token' => uniqid(),
                'token_type'   => 'some_type',
                'expires_in'   => 300,
            ]));

        $client->shouldReceive('post')
            ->once()
            ->andReturn($response);

        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());
        $httpClient->setHttp($client);

        $httpClient->generateAccessToken();

        $this->assertTrue($httpClient->hasAccessToken());
    }

    public function testIfAccessTokenRequestReturnNot200StatusCodeThenException()
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

    public function testRequest()
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

    public function testRequestIfExpiredAccessToken()
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

    public function testMagicCall()
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

    public function testSetCacheManager()
    {
        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());

        $cacheManager = Mockery::mock(AccessTokenCacher::class);

        $this->assertInstanceOf(
            \Angecode\IproSoftware\Contracts\HttpClient::class,
            $httpClient->setCacheManager($cacheManager)
        );
    }

    public function testGetConfig()
    {
        $httpClient = new HttpClient($this->clientCredentials, new NoneCacher());

        $this->assertNotEmpty($httpClient->getConfig());

        $this->setProtectedProperty($httpClient, 'http', null);

        $this->expectException(IproSoftwareApiException::class);

        $httpClient->getConfig();
    }
}
