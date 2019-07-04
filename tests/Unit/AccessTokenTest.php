<?php

namespace Angecode\IproSoftware\Tests\Unit;

use Angecode\IproSoftware\AccessToken\AccessToken;
use Angecode\IproSoftware\Tests\TestCase;
use Carbon\Carbon;

class AccessTokenTest extends TestCase
{

    public function testIfAccessTokenValid()
    {
        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addDay()->toString());

        $this->assertTrue($accessToken->hasAccessToken());
    }

    public function testIfAccessTokenInvalidIfEmptyToken()
    {
        $accessToken = new AccessToken('', 'some_type', '100', Carbon::now()->addDay()->toString());

        $this->assertFalse($accessToken->hasAccessToken());
    }

    public function testIfAccessTokenInvalidIfExpired()
    {
        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->subMinute()->toString());

        $this->assertFalse($accessToken->hasAccessToken());
    }

    public function testIfAccessTokenExpired()
    {
        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->subMinute()->toString());

        $this->assertTrue($accessToken->isTokenExpired());

        $accessToken = new AccessToken(uniqid(), 'some_type', '100', Carbon::now()->addMinute()->toString());

        $this->assertFalse($accessToken->isTokenExpired());
    }

    public function testIfAccessTokenMakeFromJson()
    {
        $accessToken = AccessToken::makeFromJson(json_encode([
            'access_token' => uniqid(),
            'token_type' => 'some_type',
            'expires_in' => '100',
            'expires_at' => Carbon::now()->addMinute()->toString()
        ]));

        $this->assertTrue($accessToken->hasAccessToken());
    }

    public function testIfAccessTokenMakeFromJsonError()
    {
        $accessToken = AccessToken::makeFromJson(json_encode([
            'token_type' => 'some_type',
            'expires_in' => '100',
            'expires_at' => Carbon::now()->addMinute()->toString()
        ]));

        $this->assertFalse($accessToken->hasAccessToken());
    }


    public function testIfAccessTokenJsonSerialize()
    {
        $data = [
            'access_token' => uniqid(),
            'token_type' => 'some_type',
            'expires_in' => '100',
            'expires_at' => Carbon::now()->addMinute()->toString()
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
            'expires_at' => Carbon::now()->addMinute()->toString()
        ];


        $accessToken = AccessToken::makeFromJson(json_encode($data));


        $this->assertEquals('Some_type ' . $data['access_token'], $accessToken->getAuthorizationHeader());
    }

}