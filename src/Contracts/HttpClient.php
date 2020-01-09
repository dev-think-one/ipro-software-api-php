<?php

namespace Angecode\IproSoftware\Contracts;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClient
{
    public function setCacheManager(AccessTokenCacher $cacheManager): self;

    /**
     * @param callable|null $responseFilter
     * @example function(ResponseInterface $response, array $options, string $path, string $method){}
     * @return HttpClient
     */
    public function setResponseFilter(?callable $responseFilter): self;

    public function setHttp(ClientInterface $http): self;

    public function getConfig($option = null);

    public function request($method, $path = '', array $options = []): ResponseInterface;
}
