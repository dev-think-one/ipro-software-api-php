<?php

namespace Angecode\IproSoftware\Contracts;

interface AccessToken extends \JsonSerializable
{
    public function hasAccessToken(): bool;

    public function isTokenExpired(): bool;

    public function getAuthorizationHeader(): string;

    public static function makeFromJson(string $json): ?self;
}
