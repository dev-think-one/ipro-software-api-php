<?php

namespace IproSoftwareApi\Contracts;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClient
{
    public function setCacheManager(AccessTokenCacher $cacheManager): static;

    /**
     * @param callable|null $responseFilter
     * @example function(ResponseInterface $response, array $options, string $path, string $method){}
     * @return static
     */
    public function setResponseFilter(?callable $responseFilter): static;

    public function setHttp(ClientInterface $http): static;

    public function getConfig($option = null);

    public function request($method, $path = '', array $options = []): ResponseInterface;
}
