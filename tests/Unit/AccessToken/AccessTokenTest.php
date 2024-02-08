<?php

namespace IproSoftwareApi\Tests\Unit\AccessToken;

use Carbon\Carbon;
use GuzzleHttp\Psr7\Stream;
use IproSoftwareApi\AccessToken\AccessToken;
use IproSoftwareApi\Exceptions\IproSoftwareApiAccessTokenException;
use IproSoftwareApi\Tests\TestCase;
use Mockery;
use Psr\Http\Message\ResponseInterface;

class AccessTokenTest extends TestCase
{
    /** @test */
    public function is_access_token_valid()
    {
        $accessToken = new AccessToken(uniqid('', true), 'some_type', '100', Carbon::now()->addDay()->toString());

        $this->assertTrue($accessToken->hasAccessToken());
    }

    /** @test */
    public function access_token_invalid_if_empty_token()
    {
        $accessToken = new AccessToken('', 'some_type', '100', Carbon::now()->addDay()->toString());

        $this->assertFalse($accessToken->hasAccessToken());
    }

    /** @test */
    public function access_token_invalid_if_expired()
    {
        $accessToken = new AccessToken(uniqid('', true), 'some_type', '100', Carbon::now()->subMinute()->toString());

        $this->assertFalse($accessToken->hasAccessToken());
    }

    /** @test */
    public function is_access_token_expired()
    {
        $accessToken = new AccessToken(uniqid('', true), 'some_type', '100', Carbon::now()->subMinute()->toString());

        $this->assertTrue($accessToken->isTokenExpired());

        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addMinute()->toString());

        $this->assertFalse($accessToken->isTokenExpired());
    }

    /** @test */
    public function make_access_token_from_json()
    {
        $accessToken = AccessToken::makeFromJson(json_encode([
            'access_token' => uniqid('', true),
            'token_type'   => 'some_type',
            'expires_in'   => '100',
            'expires_at'   => Carbon::now()->addMinute()->toString(),
        ]));

        $this->assertTrue($accessToken->hasAccessToken());
    }

    /** @test */
    public function access_token_make_from_json_return_null()
    {
        $accessToken = AccessToken::makeFromJson('{}');

        $this->assertNull($accessToken);
    }

    /** @test */
    public function make_access_token_from_api_response()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(new Stream(fopen('data://text/plain;base64,' . base64_encode(json_encode([
                    'access_token' => uniqid(),
                    'token_type'   => 'some_type',
                    'expires_in'   => 500,
                ])), 'r')));

        $accessToken = AccessToken::makeFromApiResponse($response);

        $this->assertInstanceOf(\IproSoftwareApi\Contracts\AccessToken::class, $accessToken);
        $this->assertFalse($accessToken->isTokenExpired());
        $this->assertTrue($accessToken->hasAccessToken());
    }

    /** @test */
    public function is_access_token_json_serialize()
    {
        $data = [
            'access_token' => uniqid('', true),
            'token_type'   => 'some_type',
            'expires_in'   => '100',
            'expires_at'   => Carbon::now()->addMinute()->toString(),
        ];

        $encodedData = json_encode($data);

        $accessToken = AccessToken::makeFromJson($encodedData);

        $this->assertTrue($accessToken->hasAccessToken());

        $this->assertEqualsCanonicalizing($data, $accessToken->jsonSerialize());

        $this->assertEquals($encodedData, json_encode($accessToken));
    }

    /** @test */
    public function access_token_auth_header()
    {
        $data = [
            'access_token' => uniqid(),
            'token_type'   => 'some_type',
            'expires_in'   => '100',
            'expires_at'   => Carbon::now()->addMinute()->toString(),
        ];

        $accessToken = AccessToken::makeFromJson(json_encode($data));

        $this->assertEquals('Some_type ' . $data['access_token'], $accessToken->getAuthorizationHeader());
    }

    /** @test */
    public function make_from_api_response_throw_exception_if_not_valid_response()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(new Stream(fopen('data://text/plain;base64,' . base64_encode(json_encode('{}')), 'r')));

        $this->expectException(IproSoftwareApiAccessTokenException::class);

        AccessToken::makeFromApiResponse($response);
    }

    /** @test */
    public function make_from_api_response_throw_exception_if_not_validt_oken()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $data = [
            'access_token' => '',
            'token_type'   => 'some_type',
            'expires_in'   => '300',
        ];

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(new Stream(fopen('data://text/plain;base64,' . base64_encode(json_encode($data)), 'r')));

        $this->expectException(IproSoftwareApiAccessTokenException::class);

        AccessToken::makeFromApiResponse($response);
    }

    /** @test */
    public function access_token_exception_contains_response_property()
    {
        $response = Mockery::mock(ResponseInterface::class);

        $data = [
            'access_token' => '',
            'token_type'   => 'some_type',
            'expires_in'   => '300',
        ];

        $response->shouldReceive('getBody')
            ->once()
            ->andReturn(new Stream(fopen('data://text/plain;base64,' . base64_encode(json_encode($data)), 'r')));

        try {
            AccessToken::makeFromApiResponse($response);
        } catch (IproSoftwareApiAccessTokenException $e) {
            $this->assertInstanceOf(ResponseInterface::class, $e->getResponse());
        }
    }
}
