<?php

namespace Angecode\IproSoftware\Tests\Unit\AccessToken;

use Angecode\IproSoftware\AccessToken\AccessToken;
use Angecode\IproSoftware\Exceptions\IproSoftwareApiAccessTokenException;
use Angecode\IproSoftware\Tests\TestCase;
use Carbon\Carbon;
use Mockery;
use Psr\Http\Message\ResponseInterface;

class AccessTokenTest extends TestCase
{
    public function testIfAccessTokenValid()
    {
        $accessToken = new AccessToken(uniqid('', true), 'some_type', '100', Carbon::now()->addDay()->toString());

        $this->assertTrue($accessToken->hasAccessToken());
    }

    public function testIfAccessTokenInvalidIfEmptyToken()
    {
        $accessToken = new AccessToken('', 'some_type', '100', Carbon::now()->addDay()->toString());

        $this->assertFalse($accessToken->hasAccessToken());
    }

    public function testIfAccessTokenInvalidIfExpired()
    {
        $accessToken = new AccessToken(uniqid('', true), 'some_type', '100', Carbon::now()->subMinute()->toString());

        $this->assertFalse($accessToken->hasAccessToken());
    }

    public function testIfAccessTokenExpired()
    {
        $accessToken = new AccessToken(uniqid('', true), 'some_type', '100', Carbon::now()->subMinute()->toString());

        $this->assertTrue($accessToken->isTokenExpired());

        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addMinute()->toString());

        $this->assertFalse($accessToken->isTokenExpired());
    }

    public function testAccessTokenMakeFromJson()
    {
        $accessToken = AccessToken::makeFromJson(json_encode([
            'access_token' => uniqid('', true),
            'token_type' => 'some_type',
            'expires_in' => '100',
            'expires_at' => Carbon::now()->addMinute()->toString(),
        ]));

        $this->assertTrue($accessToken->hasAccessToken());
    }

    public function testAccessTokenMakeFromJsonReturnNull()
    {
        $accessToken = AccessToken::makeFromJson('{}');

        $this->assertNull($accessToken);
    }

    public function testAccessTokenMakeFromApiResponse()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode([
                'access_token' => uniqid(),
                'token_type' => 'some_type',
                'expires_in' => 500,
            ]));

        $accessToken = AccessToken::makeFromApiResponse($response);

        $this->assertInstanceOf(\Angecode\IproSoftware\Contracts\AccessToken::class, $accessToken);
        $this->assertFalse($accessToken->isTokenExpired());
        $this->assertTrue($accessToken->hasAccessToken());
    }

    public function testIfAccessTokenJsonSerialize()
    {
        $data = [
            'access_token' => uniqid('', true),
            'token_type' => 'some_type',
            'expires_in' => '100',
            'expires_at' => Carbon::now()->addMinute()->toString(),
        ];

        $encodedData = json_encode($data);

        $accessToken = AccessToken::makeFromJson($encodedData);

        $this->assertTrue($accessToken->hasAccessToken());

        $this->assertEqualsCanonicalizing($data, $accessToken->jsonSerialize());

        $this->assertEquals($encodedData, json_encode($accessToken));
    }

    public function testIfAccessTokenAuthHeader()
    {
        $data = [
            'access_token' => uniqid(),
            'token_type' => 'some_type',
            'expires_in' => '100',
            'expires_at' => Carbon::now()->addMinute()->toString(),
        ];

        $accessToken = AccessToken::makeFromJson(json_encode($data));

        $this->assertEquals('Some_type ' . $data['access_token'], $accessToken->getAuthorizationHeader());
    }

    public function testMakeFromApiResponseThrowExceptionIfNotValidResponse()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn('{}');

        $this->expectException(IproSoftwareApiAccessTokenException::class);

        AccessToken::makeFromApiResponse($response);
    }

    public function testMakeFromApiResponseThrowExceptionIfNotValidToken()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $data = [
            'access_token' => '',
            'token_type' => 'some_type',
            'expires_in' => '300',
        ];

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode($data));

        $this->expectException(IproSoftwareApiAccessTokenException::class);

        AccessToken::makeFromApiResponse($response);
    }

    public function testAccessTokenExceptionHasResponse()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $data = [
            'access_token' => '',
            'token_type' => 'some_type',
            'expires_in' => '300',
        ];

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(json_encode($data));

        try {
            AccessToken::makeFromApiResponse($response);
        } catch (IproSoftwareApiAccessTokenException $e) {
            $this->assertInstanceOf(ResponseInterface::class, $e->getResponse());
        }
    }
}
