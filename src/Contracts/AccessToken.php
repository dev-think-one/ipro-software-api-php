<?php

namespace Angecode\IproSoftware\Contracts;

use Psr\Http\Message\ResponseInterface;

interface AccessToken extends \JsonSerializable
{
    public function hasAccessToken(): bool;

    public function isTokenExpired(): bool;

    public function getAuthorizationHeader(): string;

    public static function makeFromJson(string $json): ?self;

    public static function makeFromApiResponse(ResponseInterface $responseBody): ?self;
}
