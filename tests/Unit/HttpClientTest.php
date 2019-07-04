<?php

namespace Angecode\IproSoftware\Tests\Unit;

use Angecode\IproSoftware\AccessToken\AccessToken;
use Angecode\IproSoftware\AccessToken\NoneCacher;
use Angecode\IproSoftware\DTOs\ClientCredentials;
use Angecode\IproSoftware\HttpClient;
use Angecode\IproSoftware\Tests\TestCase;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Mockery;
use Psr\Http\Message\ResponseInterface;

class HttpClientTest extends TestCase
{
    protected $clientCredentials;

    protected function setUp()
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
        $client = Mockery::mock(Client::class);

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
}
