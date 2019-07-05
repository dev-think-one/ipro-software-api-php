<?php

namespace Angecode\IproSoftware\Contracts;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClient
{
    public function setCacheManager(AccessTokenCacher $cacheManager): self;

    public function setHttp(ClientInterface $http): self;

    public function getConfig($option = null);

    public function request($method, $path = '', array $options = []): ResponseInterface;
}
